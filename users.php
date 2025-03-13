<?php

require('./secrets.php');
require('./vendor/autoload.php'); // betöltjük a JWT könyvtárat

use Firebase\JWT\JWT;

// 📌 GET kérés esetén, listázza az összes felhasználót (teszteléshez!)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    return;
}

// 📌 POST kérés esetén, megnézi, hogy bejelentkezésről vagy regisztrációról van-e szó
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    // 📌 bejelentkezés (`POST /users=login`)
    if ($_GET['users'] ?? '' === 'login') {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND password = ?');
        $stmt->execute([$data->email, hash('sha256', $data->password)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            die(json_encode(["error" => "Incorrect email or password!"]));
        }

        // 📌 token generálás
        $payload = [
            "user_id" => $user['id'],
            "exp" => time() + (60 * 60) // Token lejárati idő: 1 óra
        ];
        $token = JWT::encode($payload, $secrets['jwt_secret'], 'HS256');

        echo json_encode(["token" => $token]);
        return;
    }

    // 📌 Regisztráció (`POST /users=register`)
    if ($_GET['users'] ?? '' === 'register') {
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

        // 📌 Jelszó SHA-256 hash-elése
        $hashedPassword = hash('sha256', $data->password);

        // Új felhasználó mentése
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$data->email, $hashedPassword]);

        echo json_encode(["message" => "You have successfully registered!"]);
        return;
    }
}
