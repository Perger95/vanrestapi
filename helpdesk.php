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

// Keresés az adatbázisban – Intelligensebb egyezés
$stmt = $pdo->query("SELECT question, answer FROM faq");
$faqItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bestMatch = null;
$bestScore = 0; // Legjobb egyezés százalékosan

// Gyakori szavak listája (stopwords)
$stopwords = ["tudok", "Mi","lehet", "van", "hogyan", "módon", "kell", "csinálni", "meg", "itt", "?", "!"];

//  Normalizáló függvény – kisbetűsítés + stopwords eltávolítás + felesleges karakterek törlése
function normalizeText($text, $stopwords) {
    $text = strtolower(trim($text)); // Kisbetűsítés és felesleges szóközök törlése
    $text = preg_replace('/[^a-z0-9áéíóöőúüű ]/i', '', $text); // Speciális karakterek eltávolítása
    $words = explode(" ", $text); // Szavak szétbontása
    $filteredWords = array_diff($words, $stopwords); // Gyakori szavak eltávolítása
    return implode(" ", $filteredWords); // Összerakás szöveggé
}

$userQuestion = normalizeText($data->question, $stopwords);

foreach ($faqItems as $faq) {
    $faqQuestion = normalizeText($faq['question'], $stopwords);

    //  Szórendi egyezés és hasonlóság számítás
    similar_text($userQuestion, $faqQuestion, $percent);
    
    //  Részleges egyezés – ha az egyik kérdés tartalmazza a másikat
    if (strpos($faqQuestion, $userQuestion) !== false || strpos($userQuestion, $faqQuestion) !== false) {
        $percent = 100; // Teljes egyezésnek vesszük
    }

    if ($percent > $bestScore) {
        $bestScore = $percent;
        $bestMatch = $faq;
    }
}

// Ha az egyezés elér egy bizonyos szintet, akkor a FAQ választ adjuk vissza
if ($bestMatch && $bestScore > 50) { // 🔹 Most még tágabb: 50% vagy jobb
    echo json_encode(["answer" => $bestMatch['answer']]);
    return;
}

// Ha nincs jó találat, AI válaszol



// AI API hívás (ha nincs találat az adatbázisban)
$client = new Client();
$response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $secrets['google_gemini_api_key'], [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'json' => [
        "contents" => [
            [
                "parts" => [
                    // Rejtett prompt (user nem látja)
                    ["text" => "Fontos! Kérlek mindig rövid választ adj, maximum 2 mondatban! "],
                    // Felhasználó kérdése
                    ["text" => $data->question]
                ]
            ]
        ]
    ]
]);

$aiResponse = json_decode($response->getBody(), true);
$aiAnswer = $aiResponse['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not find an answer.';

echo json_encode(["answer" => $aiAnswer]);

?>