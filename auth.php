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

// 🔑 Token ellenőrzés
$headers = apache_request_headers();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    http_response_code(401);
    die(json_encode(["error" => "Authorization error"]));
}

if (strpos($token, 'Bearer ') === 0) {
    $token = substr($token, 7);
}

try {
    $decoded = JWT::decode($token, new Key($secrets['jwt_secret'], 'HS256'));
    $userId = $decoded->user_id;
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(["error" => "Authorization error"]));
}
return true;
