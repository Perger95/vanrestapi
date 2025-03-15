<?php

require('./secrets.php'); // betöltjük az secrets.php-t
require('./auth.php');   // betöltjük az auth.php-t
require('./vendor/autoload.php'); // betöltjük a JWT könyvtárat
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// adatbázis kapcsolat létrehozás
$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // hibaüzenet, ha valami nem működne

// HTTP metódus beolvasás
$method = $_SERVER['REQUEST_METHOD'];


//                                      POST 

if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    if (!isset($data->title) || !isset($data->occurrence)) {
        http_response_code(400);
        die(json_encode(["error" => "You must fill Title and Occurrence data!"]));
    }

    $stmt = $pdo->prepare('INSERT INTO events (user_id, title, occurrence, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $userId, 
        $data->title, 
        $data->occurrence, 
        isset($data->description) && trim($data->description) !== '' ? $data->description : null
    ]);

    echo json_encode(["message" => "Event successfully created!"]);
    return;
}

//                              GET

if ($method == 'GET') {
    // 📌 felhasználó saját eseményeinek listázása

    // lekérdezzük az adott felhasználóhoz tartozó eseményeket
    $stmt = $pdo->prepare('SELECT * FROM events WHERE user_id = ?');
    $stmt->execute([$userId]); // UserId-t JWT tokenből kell kiolvasni

    // az eseményeket JSON formátumban visszaküldjük a kliensnek
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events);
    return;
}


//                                                         PATCH
//                                            Esemény leírásának frissítése

if ($method == 'PATCH') {
    header('Content-Type: application/json');

    error_log("🔄 PATCH request received!");

    if (!isset($_GET['events'])) { 
        http_response_code(400);
        die(json_encode(["error" => "Missing event ID!"]));
    }

    $eventId = $_GET['events']; 
    $data = json_decode(file_get_contents('php://input'));

    error_log("🔍 Event ID: $eventId, User ID: $userId, Data: " . json_encode($data));

    if (!isset($data->description)) {
        http_response_code(400);
        die(json_encode(["error" => "You must add a description!"]));
    }

    $stmt = $pdo->prepare('UPDATE events SET description = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$data->description, $eventId, $userId]);

    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        die(json_encode(["error" => "This event cannot be modified!"]));
    }

    echo json_encode(["message" => "Event has been updated!"]);
    return;
}



//                                          DELETE
//                                    Esemény törlése

error_log("🛠️ DELETE kérés érkezett, ID: " . $_GET['id']);
error_log("🛠️ Felhasználói azonosító: " . $userId);

if ($method == 'DELETE') {
    header('Content-Type: application/json');

    parse_str($_SERVER['QUERY_STRING'], $queryParams);

    if (!isset($queryParams['id']) || empty($queryParams['id'])) {
        http_response_code(400);
        die(json_encode(["error" => "Missing event ID!"]));
    }

    $eventId = (int)$queryParams['id'];

    // töröljük az eseményt, de csak ha az adott user hozta létre
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ? AND user_id = ?');
    $stmt->execute([$eventId, $userId]);

    // ha nem történt törlés (pl. rossz ID vagy más user eseménye)
    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        die(json_encode(["error" => "This event cannot be deleted!"]));
    }

    echo json_encode(["message" => "Event successfully deleted!"]);
    return;
}