<?php

require('./secrets.php');

$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

// CORS beállítások
//header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
//header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Ha OPTIONS kérés érkezik, válaszoljunk, de NE lépjünk ki!
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    return; // 🔥 `exit;` HELYETT `return;`, hogy folytatódjon a kód!
}

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

if ($resource == 'helpdesk') {
  require('helpdesk.php');
}


 // Ki kellett szednem, mert duplikált JSON objektumot kaptam a válasz mellé és zavaró volt
//if (isset($data) && !in_array($_GET['helpdesk'] ?? '', ['helpdesk', 'users', 'events'])) { 
//header('Content-Type: application/json'); 
//echo json_encode($data);
//}