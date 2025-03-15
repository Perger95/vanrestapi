<?php
require('./vendor/autoload.php'); // JWT betöltés
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 204 No Content");
    return;
}

// Ha nincs query string, ne kérjen auth-ot
if (empty($_SERVER['QUERY_STRING'])) {
    return true;
}

// 🔹 Feketelista kivételei (autentikáció NEM kell ezekhez a végpontokhoz)
$noAuthResources = [
    'GET' => ['new-password', 'reset-password'],
    'POST' => ['users','new-password', 'reset-password', 'login'], // 🔹 Hozzáadtuk a `login` végpontot!
    'PATCH' => [],
    'DELETE' => []
];

// Query feldolgozása
parse_str($_SERVER['QUERY_STRING'], $queryParams);
$resource = $queryParams['users'] ?? strtok($_SERVER['QUERY_STRING'], '=');

// 🔍 Ha a végpont az engedélyezett lista része, engedjük tovább
if (in_array($resource, $noAuthResources[$_SERVER['REQUEST_METHOD']] ?? [])) {
    return true;
}

// 🔑 Token ellenőrzés (Authorization fejléc)
$headers = getallheaders();
$token = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "Missing authorization token."]);
    exit;
}

// Ha a token "Bearer " előtaggal van küldve, levágjuk
if (strpos($token, 'Bearer ') === 0) {
    $token = substr($token, 7);
}

try {
    // Token dekódolás
    $decoded = JWT::decode($token, new Key($secrets['jwt_secret'], 'HS256'));
    $userId = $decoded->user_id;

    // 🔹 Token ellenőrzés az adatbázisban (a PDO már az index.php-ban van!)
    global $pdo;
    $stmt = $pdo->prepare('SELECT token, token_expires FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $userTokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userTokenData) {
        http_response_code(401);
        echo json_encode(["error" => "User not found."]);
        exit;
    }

    // Ellenőrizzük, hogy az adatbázisban lévő token egyezik-e a küldött tokennel
    if ($userTokenData['token'] !== $token) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token. Please log in again."]);
        exit;
    }

    // Ellenőrizzük, hogy a token nem járt-e le
    if (strtotime($userTokenData['token_expires']) < time()) {
        http_response_code(401);
        echo json_encode(["error" => "Token expired. Please log in again."]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token.", "details" => $e->getMessage()]);
    exit;
}

return true;