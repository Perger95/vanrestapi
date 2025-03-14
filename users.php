<?php

require('./secrets.php');
require('./vendor/autoload.php'); // betöltjük a JWT könyvtárat

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// GET - listázza az összes felhasználót (teszteléshez!)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    return;
}

// POST kérés esetén, megnézi, hogy bejelentkezésről vagy regisztrációról van-e szó
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $requestUser = trim($_GET['users'] ?? '', " ?");

    error_log("Requested endpoint after trim: " . $requestUser);

    //// BEJELENTKEZÉS (POST /users=login)
    if ($requestUser === 'login') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare('SELECT failed_attempts, last_attempt FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Ha már volt sikertelen próbálkozás
        if ($attempt) {
            $failedAttempts = $attempt['failed_attempts'];
            $lastAttempt = strtotime($attempt['last_attempt']);
            $currentTime = time();
    
            // Ha 5 percen belül 5 sikertelen próbálkozás volt, tiltás
            if ($failedAttempts >= 5 && ($currentTime - $lastAttempt) < 5) {
                http_response_code(429);
                die(json_encode(["error" => "Too many failed login attempts. Try again later."]));
            }
        
            
        }
    
         // Jelszóhash ellenőrzése bcrypt-tel
         $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
         $stmt->execute([$data->email]);
         $user = $stmt->fetch(PDO::FETCH_ASSOC);
     
         if (!$user || !password_verify($data->password, $user['password'])) {
             if ($attempt) {
                 $stmt = $pdo->prepare('UPDATE login_attempts SET failed_attempts = failed_attempts + 1, last_attempt = NOW() WHERE ip_address = ?');
                 $stmt->execute([$ip]);
             } else {
                 $stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, failed_attempts, last_attempt) VALUES (?, 1, NOW())');
                 $stmt->execute([$ip]);
             }
     
             http_response_code(401);
             die(json_encode(["error" => "Incorrect email or password!"]));
        }
    
        // Siker esetén reseteljük a próbálkozások számát
        $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        
        // Token generálása
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + (60 * 60)
        ];
        $token = JWT::encode($payload, $secrets['jwt_secret'], 'HS256');
    
        echo json_encode(["token" => $token]);
        return;
    }
    

    ///// REGISZTRÁCIÓ (`POST /users=register`)
    if ($requestUser === 'register') {
        if (!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            die(json_encode(["error" => "Email and password are required!"]));
        }

        // Ellenőrizzük, hogy már létezik-e a felhasználó
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data->email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            die(json_encode(["error" => "This Email is already used."]));
        }

        // Új felhasználó mentése
        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$data->email, $hashedPassword]);
    
        echo json_encode(["message" => "Successfully registered!"]);
        return;
    }

    ///// Jelszó visszaállítás kérés
    if ($requestUser === 'reset-password') {
        if (!isset($data->email)) {
            http_response_code(400);
            die(json_encode(["error" => "Email is required"]));
        }
    
        // Ellenőrizzük, hogy az email létezik-e az adatbázisban
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data->email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            http_response_code(400);
            die(json_encode(["error" => "Email address not found"]));
        }
    
        // Jelszó-visszaállító token generálása (64 karakter)
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 percig érvényes
    
        // Token mentése az adatbázisba
        $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?');
        $stmt->execute([hash('sha256', $resetToken), $expiresAt, $data->email]);
    
        // Válasz a felhasználónak (Normál esetben emailt küldenénk)
        echo json_encode(["message" => "We've sent you an email! Please check your inbox to reset your password. (But not for real  because its a project)", "reset_token" => $resetToken]);
        return;
    }
    
    if ($requestUser === 'new-password') {
        if (!isset($data->token) || !isset($data->new_password)) {
            http_response_code(400);
            die(json_encode(["error" => "You must enter a new password and you must have a valid Token!"]));
        }
    
        // Ellenőrizzük, hogy a token létezik és nem járt le
        $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()');
        $stmt->execute([hash('sha256', $data->token)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            http_response_code(400);
            die(json_encode(["error" => "Invalid or expired token!"]));
        }
    
        // 🔥 Új jelszó bcrypt hash-elése
        $hashedPassword = password_hash($data->new_password, PASSWORD_BCRYPT);
    
        // Frissítjük a jelszót az adatbázisban és töröljük a reset tokent
        $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $stmt->execute([$hashedPassword, $user['id']]);

        echo json_encode(["message" => "New Password has been successfully created"]);
        return;
    }
    
}
