<?php

require('./cors.php');
require('./secrets.php');

$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

if ($development) {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
}

$resource = strtok($_SERVER['QUERY_STRING'], '=');
require('auth.php');

// Engedélyezzük a PATCH és DELETE metódusokat, ha az Apache nem támogatja őket
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
    echo json_encode($data);
}
