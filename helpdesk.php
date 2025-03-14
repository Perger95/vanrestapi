<?php

require('./secrets.php'); // DB kapcsolat
require('./vendor/autoload.php'); // Ha szükséges az AI API-hoz

use GuzzleHttp\Client; // AI API híváshoz

$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["error" => "Method not allowed"]));
}

// JSON input beolvasása
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->question)) {
    http_response_code(400);
    die(json_encode(["error" => "You must ask a question!"]));
}

// 🔍 Keresés az adatbázisban
$stmt = $pdo->prepare("SELECT answer FROM faq WHERE question LIKE ?");
$stmt->execute(["%" . $data->question . "%"]);
$faq = $stmt->fetch(PDO::FETCH_ASSOC);

if ($faq) {
    echo json_encode(["answer" => $faq['answer']]);
    return;
}

// 🔥 AI API hívás (ha nincs találat)
$client = new Client();
$response = $client->post('https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill', [
    'headers' => [
        'Authorization' => 'Bearer ' . $secrets['huggingface_api_key'],
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'inputs' => $data->question // Ezt használja a Hugging Face
    ]
]);

$aiResponse = json_decode($response->getBody(), true);
$aiAnswer = $aiResponse['choices'][0]['message']['content'] ?? 'Sorry, I could not find an answer. Would you like to talk to an operator?';

echo json_encode(["answer" => $aiAnswer]);

?>
