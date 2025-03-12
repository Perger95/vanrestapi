<?php

require('./secrets.php');
require('./auth.php');



// adatbázis kapcsolat létrehozása
$pdo = new PDO('mysql:host=localhost;dbname=' . $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Hiba dobása, ha valami elromlik

// HTTP metódus beolvasása
$method = $_SERVER['REQUEST_METHOD'];


//                                      POST 

if ($method == 'POST') {
    //  POST esetén, azaz új esemény létrehozásánál beolvaszuk a kliens által küldött JSON adatokat
    $data = json_decode(file_get_contents('php://input'));

    // Ellenőrizzük, hogy a kötelező mezők (title, occurrence) meg vannak-e adva
    if (!isset($data->title) || !isset($data->occurrence)) {
        http_response_code(400); // 400 Bad Request hiba
        die(json_encode(["error" => "Title és Occurrence kötelező!"]));
    }

    // Az eseményt beszúrjuk az adatbázisba (user_id egyelőre fix érték, ezt később JWT-vel kezeljük)
    $stmt = $pdo->prepare('INSERT INTO events (user_id, title, occurrence, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([1, $data->title, $data->occurrence, $data->description ?? null]);

    // Visszaküldjük a sikeres válasz JSON formátumban
    header('Content-Type: application/json');
    echo json_encode(["message" => "Esemény sikeresen létrehozva!"]);
    return;
}


//                              GET

if ($method == 'GET') {
    // 📌 Felhasználó saját eseményeinek listázása

    // Lekérdezzük az adott felhasználóhoz tartozó eseményeket
    $stmt = $pdo->prepare('SELECT * FROM events WHERE user_id = ?');
    $stmt->execute([1]); // A későbbiekben ezt JWT tokenből kell kiolvasni

    // Az eseményeket JSON formátumban visszaküldjük a kliensnek
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events);
    return;
}


//                                                         PATCH
//                                            Esemény leírásának frissítése

if ($method == 'PATCH') {
    header('Content-Type: application/json');
    
    // Ellenőrizzük, hogy van-e ID a kérésben
    if (!isset($_GET['id'])) {
        http_response_code(400);
        die(json_encode(["error" => "Hiányzó esemény ID!"]));
    }

    $eventId = $_GET['id'];

    // Beolvaszuk a kliens által küldött JSON adatokat
    $data = json_decode(file_get_contents('php://input'));

    // Ellenőrizzük, hogy van-e description mező
    if (!isset($data->description)) {
        http_response_code(400);
        die(json_encode(["error" => "Hiányzó description mező!"]));
    }

    // Frissítjük az esemény leírását az adatbázisban
    $stmt = $pdo->prepare('UPDATE events SET description = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$data->description, $eventId, 1]); // JWT után a user_id dinamikus lesz

    // Ha nem történt frissítés (pl. rossz ID vagy más user eseménye)
    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        die(json_encode(["error" => "Nem módosítható az esemény!"]));
    }

    echo json_encode(["message" => "Esemény frissítve!"]);
    return;
}




//                                          DELETE
//                                    Esemény törlése

if ($method == 'DELETE') {
    header('Content-Type: application/json');

    // Ellenőrizzük, hogy van-e ID a kérésben
    if (!isset($_GET['id'])) {
        http_response_code(400);
        die(json_encode(["error" => "Hiányzó esemény ID!"]));
    }

    $eventId = $_GET['id'];

    // Töröljük az eseményt, de csak ha az adott user hozta létre
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ? AND user_id = ?');
    $stmt->execute([$eventId, 1]); // JWT után a user_id dinamikus lesz

    // Ha nem történt törlés (pl. rossz ID vagy más user eseménye)
    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        die(json_encode(["error" => "Nem törölhető az esemény!"]));
    }

    echo json_encode(["message" => "Esemény törölve!"]);
    return;
}

