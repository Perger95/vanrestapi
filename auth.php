<?php

require('./vendor/autoload.php'); // betöltjük JWT könyvtárat
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// minden OPTIONS REQUEST-et továbbenged
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    return true;
}

// ha nincs query string, ne kérjen auth-ot
if (empty($_SERVER['QUERY_STRING'])) {
    return true;
}

// feketelista kivételei, itt sem kell auth
$noAuthResources = [
    'GET' => ['products'],
    'POST' => ['users','helpdesk'],
    'PATCH' => [],
    'DELETE' => []
];

// az első paraméter legyen kiválasztva az URL-ből
$resource = strtok($_SERVER['QUERY_STRING'], '=');

// ha az adott HTTP metódusnál a resource benne van az engedélyezett listában, engedjük tovább
if (in_array($resource, $noAuthResources[$_SERVER['REQUEST_METHOD']])) {
    return true;
}

// Token ellenőrzés
$headers = apache_request_headers();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    http_response_code(401);
    die('Authorization error');   // ha nincs token nincs auth
}

try {
    $decoded = JWT::decode($token, new Key($secrets['jwt_secret'], 'HS256'));
    $userId = $decoded->user_id; // megkapjuk a felhasználó azonosítóját a tokenből
} catch (Exception $e) {
    http_response_code(401);
    die('Authorization error');
}

return true;