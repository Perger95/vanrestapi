<?php

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