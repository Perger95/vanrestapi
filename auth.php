<?php

// allow all OPTIONS REQUEST
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    return true;
}

// list resources what does not need auth
$noAuthResorces = [
    'GET' => ['products'],
    'POST' => ['users=login'],
    'PATCH' => [],
    'DELETE' => []
];

if (in_array($_SERVER['QUERY_STRING'], $noAuthResorces[$_SERVER['REQUEST_METHOD']])) {
    return true;
}

// check the token

http_response_code(401);
die('Authorizaton error');