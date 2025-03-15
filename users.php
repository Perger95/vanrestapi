<?php

require('./secrets.php');
require('./vendor/autoload.php'); // Betöltjük a JWT és PHPMailer könyvtárakat

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adatbázis kapcsolat inicializálása
$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

// POST kérés - bejelentkezés / regisztráció / jelszó visszaállítás
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $requestUser = trim($_GET['users'] ?? '', " ?");

    error_log("Requested endpoint after trim: " . $requestUser);

    //// 🔑 **BEJELENTKEZÉS**
    if ($requestUser === 'login') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare('SELECT failed_attempts, last_attempt FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ellenőrzés a túl sok próbálkozás ellen
        if ($attempt) {
            $failedAttempts = $attempt['failed_attempts'];
            $lastAttempt = strtotime($attempt['last_attempt']);
            $currentTime = time();

            if ($failedAttempts >= 5 && ($currentTime - $lastAttempt) < 180) {
                http_response_code(429);
                die(json_encode(["error" => "Too many failed login attempts. Try again later."]));
            }
        }

        // Jelszó ellenőrzés bcrypt-tel
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

        // Sikeres bejelentkezés esetén a próbálkozásokat töröljük
        $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
        $stmt->execute([$ip]);

        // Token generálás
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + (60 * 60)
        ];
        $token = JWT::encode($payload, $secrets['jwt_secret'], 'HS256');

        echo json_encode(["token" => $token]);
        return;
    }

    //// 📝 **REGISZTRÁCIÓ (`POST /users=register`)**
    if ($requestUser === 'register') {
        if (!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            die(json_encode(["error" => "Email and password are required!"]));
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data->email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            die(json_encode(["error" => "This Email is already used."]));
        }

        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$data->email, $hashedPassword]);

        echo json_encode(["message" => "Successfully registered!"]);
        return;
    }

    //// 🔄 **JELSZÓ-VISSZAÁLLÍTÁS KÉRÉS (`POST /users=reset-password`)**
    if ($requestUser === 'reset-password') {
        if (!isset($data->email)) {
            http_response_code(400);
            die(json_encode(["error" => "Email is required"]));
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$data->email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(400);
            die(json_encode(["error" => "Email address not found"]));
        }

        // Token generálás
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 percig érvényes

        $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?');
        $stmt->execute([hash('sha256', $resetToken), $expiresAt, $data->email]);

        // 🔹 **EMAIL KÜLDÉS**
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'smtpfortestingprgr@gmail.com';
            $mail->Password = $GLOBALS['secrets']['reset_email_agent'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('smtpfortestingprgr@gmail.com', 'Password Reset');
            $mail->addAddress($data->email);

            $resetLink = "https://localhost:5173/reset-password?token=" . $resetToken;
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "<h3>Password Reset Request</h3>
                            <p>Click the link below to reset your password:</p>
                            <a href='$resetLink'>$resetLink</a>
                            <p>This link is valid for 5 minutes.</p>";

            $mail->send();
            echo json_encode(["message" => "We've sent you an email with password reset instructions."]);

        } catch (Exception $e) {
            echo json_encode(["error" => "Email sending failed: " . $mail->ErrorInfo]);
        }

        return;
    }

//// 🔄 **ÚJ JELSZÓ BEÁLLÍTÁSA (`POST /users=new-password`)**
if ($requestUser === 'new-password') {
    if (!isset($data->token) || !isset($data->new_password)) {
        http_response_code(400);
        die(json_encode(["error" => "Token and new password are required!"]));
    }

    $hashedToken = hash('sha256', $data->token);
    
    // Ellenőrizzük, hogy a token létezik és érvényes
    $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()');
    $stmt->execute([$hashedToken]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        die(json_encode(["error" => "Invalid or expired token"]));
    }

    // 🔒 **Új jelszó bcrypt hash-elése**
    $hashedPassword = password_hash($data->new_password, PASSWORD_BCRYPT);

    // Jelszó frissítése az adatbázisban + reset token törlése
    $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
    $stmt->execute([$hashedPassword, $user['id']]);

    echo json_encode(["message" => "Password has been successfully reset."]);
    return;
}


    
// GET - listázza az összes felhasználót (csak teszteléshez!)
//if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    return;
//}


}

