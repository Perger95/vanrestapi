<?php

require('./vendor/autoload.php'); // betöltjük JWT könyvtárat
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {  //PRÓBA SZERENCSE  vagy vissza is állíthatod VANILLÁSRA!
    header("HTTP/1.1 204 No Content"); // Visszaküldünk egy megfelelő választ
    return;
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

error_log("🔍 auth.php is processing request | Method: " . $_SERVER['REQUEST_METHOD']);  // EZ MEG VALAMI SZAR VERZIÓ (ÚJ DEBUG)
// Debugging: Kizárólag a POST /events végpont esetén logoljuk a kapott tokent!
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $resource == 'events') {
}

// Token ellenőrzés
$headers = apache_request_headers();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    http_response_code(401);
    die('Authorization error');   // ha nincs token nincs auth
}

if (strpos($token, 'Bearer ') === 0) {
    $token = substr($token, 7);
}

error_log("🔑 Received token: " . $token); // DEBUG MIATT TÖRLHETED MAJD

try {
    $decoded = JWT::decode($token, new Key($secrets['jwt_secret'], 'HS256'));     // EZ MEG VALAMI SZAR VERZIÓ (ÚJ DEBUG)
    error_log("✅ Token successfully decoded! User ID: " . $decoded->user_id);
    $userId = $decoded->user_id; // megkapjuk a felhasználó azonosítóját a tokenből
} catch (Exception $e) {
    error_log("❌ Token decoding failed: " . $e->getMessage());
    http_response_code(401);
    die(json_encode(["error" => "Authorization error"]));
}
//try {                                                                                             EZ A JÓ VERZIÓ
//   $decoded = JWT::decode($token, new Key($secrets['jwt_secret'], 'HS256'));
//    $userId = $decoded->user_id; // megkapjuk a felhasználó azonosítóját a tokenből
//    error_log("Received token: " . $token);

//} catch (Exception $e) {
 //   http_response_code(401);
 //   die('Authorization error');
//}

return true;