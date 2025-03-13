<?php

require('./secrets.php');
require('./vendor/autoload.php'); // betöltjük a JWT könyvtárat

use Firebase\JWT\JWT;

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

    // Tisztítsuk meg az URL paramétert
    $requestUser = trim($_GET['users'] ?? '', " ?");

    error_log("Requested endpoint after trim: " . $requestUser);

    //// BEJELENTKEZÉS (`POST /users=login`)
    if ($requestUser === 'login') {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND password = ?');
        $stmt->execute([$data->email, hash('sha256', $data->password)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            die(json_encode(["error" => "Incorrect email or password!"]));
        }

        // token generálás
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + (60 * 60) // Token lejárati idő: 1 óra
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
            die(json_encode(["error" => "This Email is already used by someone!"]));
        }

        // Jelszó SHA-256 hash-elése
        $hashedPassword = hash('sha256', $data->password);

        // Új felhasználó mentése
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$data->email, $hashedPassword]);

        echo json_encode(["message" => "You have successfully registered!"]);
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
    
        // Új jelszó hash-elése és frissítése az adatbázisban
        $hashedPassword = hash('sha256', $data->new_password);
        $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $stmt->execute([$hashedPassword, $user['id']]);
    
        echo json_encode(["message" => " New Password has been successfully created"]);
        return;
    }
    
}
