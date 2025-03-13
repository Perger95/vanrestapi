<?php

require('./vendor/autoload.php'); // betöltjük a JWT könyvtárat
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

// feketelista kivételei
$noAuthResources = [
    'GET' => ['products'],
    'POST' => ['users'],
    'PATCH' => [],
    'DELETE' => []
];

// csak az első paraméter legyen kiválasztva az URL-ből
$resource = strtok($_SERVER['QUERY_STRING'], '=');

// ha az adott HTTP metódusnál a resource benne van az engedélyezett listában, engedjük tovább
if (in_array($resource, $noAuthResources[$_SERVER['REQUEST_METHOD']])) {
    return true;
}

// Token ellenőrzése
$headers = apache_request_headers();
$token = $headers['Authorization'] ?? null;