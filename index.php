<?php

require('./secrets.php');

$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

// Ha OPTIONS kérés érkezik, válaszoljunk, de NE lépjünk ki!
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ellenőrizzük, hogy az API kérések HTTPS-en futnak-e
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    http_response_code(403);
    die(json_encode(["error" => "Only HTTPS connections are allowed."]));
}

// ** FONTOS ** Egyszerűsített query kezelés
$resource = strtok($_SERVER['QUERY_STRING'], '=');
require('auth.php');

// 🔍 Ellenőrizzük, hogy a `$resource` változó valóban tartalmaz-e értéket
error_log("🛠️ API Request received: " . $resource);

// Query paraméterek feldolgozása
parse_str($_SERVER['QUERY_STRING'], $queryParams);

// Erőforrás kiválasztása (az első kulcs alapján)
$resource = array_key_first($queryParams);

// A megfelelő fájlok betöltése az API végpontok szerint
if ($resource == 'users') {
    require('users.php');
}
if ($resource == 'events') {
    require('events.php'); 
}
if ($resource == 'helpdesk') {
    require('helpdesk.php');
}
