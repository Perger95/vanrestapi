<?php

require('./cors.php');
require('./secrets.php');

$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
  http_response_code(403);
  die(json_encode(["error" => "Only HTTPS connections are allowed."]));
}

if ($development) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
}

$resource = strtok($_SERVER['QUERY_STRING'], '=');
require('auth.php');

// Itt erőltetve engedélyeztem a PATCH és DELETE metódusokat, mert teszteléskor olyan volt, mintha az Apache nem támogatná őket
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
}


if ($resource == 'products') {
  require('products.php');
}
if ($resource == 'users') {
  require('users.php');
}
if ($resource == 'events') {
    require('events.php'); 
}

if (isset($data)) {
  header('Content-Type: application/json'); // JSON fejlécet kérek, hogy ne kaphassak null értéket
  echo json_encode($data);
}
