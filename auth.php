<?php

// engedélyezzen minden OPTIONS REQUEST-et
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    return true;
}

//  ha nincs query string, ne kérjen auth-ot
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

// az első paraméter legyen kiválasztva az URL-ből
$resource = strtok($_SERVER['QUERY_STRING'], '=');

// ha az adott HTTP metódusnál a resource benne van az engedélyezett listában, engedjük tovább
if (in_array($resource, $noAuthResources[$_SERVER['REQUEST_METHOD']])) {
    return true;
}

// token check
$token = isset(apache_request_headers()['Token']) ? apache_request_headers()['Token'] : null;

$stmt = $pdo->prepare('SELECT id FROM users WHERE token = ?');
$stmt->execute([$token]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    return true;
}

http_response_code(401);
die('Authorization error');