<?php

require('./secrets.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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


if ($resource == 'users') {
  require('users.php');
}
if ($resource == 'events') {
    require('events.php'); 
}

if (isset($data)) {
  header('Content-Type: application/json'); 
  echo json_encode($data);
}
