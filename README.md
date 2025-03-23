# Vanilla PHP REST API - UCC project - Eseménykezelő rendszer**

Ez egy egyszerű REST API, amit Vanilla PHP és MySQL segítségével készítettem el. Egy eseménykezelő rendszer létrehozása volt a célom, amelyen keresztül a felhasználók eseményeket hozhatnak létre, listázhatják, szerkeszthetik és törölhetik azokat.
A rendszer több felhasználót is képes kezelni és minden felhasználó csak a hozzá kapcsolódó eseményeket képes kezelni. 
------

## ** Felhasznált technológiák**  
    
    Backend:

PHP (Vanilla, framework nélkül)

MySQL (phpMyAdmin, XAMPP-on keresztül)

JWT autentikáció (felhasználói hitelesítéshez)

SQL Injection védelem (prepared statements)

Gmail SMTP (jelszó-visszaállító e-mailek küldéséhez)

Google Gemini AI (FAQ és chatbot válaszadás)

    Frontend:

Vite + React (modern, gyors fejlesztőkörnyezet)

React Router (oldalváltások kezelése)

Fetch API (backend kommunikációhoz)

Bootstrap / CSS styling (stílusokhoz)

Böngésző DevTools (Debughoz)

    Fejlesztői eszközök:

Insomnia (REST API teszteléshez)

XAMPP (Apache szerver és MySQL adatbázis futtatására)

Git & GitHub (verziókezeléshez)

--- 

## Endpoints (végpontok)

|  URL                  | HTTP METHOD | AUTH | JSON Response       |
|-----------------------|-------------|------|---------------------|
| ?users=login          | POST        |      | user's token        |
| ?users=register       | POST        |      | new user            |
| ?users=reset-password | POST        |      | reset token->email  |
| ?users=new-password   | POST        |      | edit  user pw       |

|  URL           | HTTP METHOD | AUTH | JSON Response       |
|----------------|-------------|------|---------------------|
| ?products      | GET         |      | all products        |
| ?products      | POST        |  Y   | new product added   |
| ?products      | PATCH       |  Y   | edited product      |
| ?products      | DELETE      |  Y   | true / false        |


|  URL           | HTTP METHOD | AUTH | JSON Response       |
|----------------|-------------|------|---------------------|
| ?events        | GET         |  Y   | user's events       |
| ?events        | POST        |  Y   | new event added     |
| ?events/:id    | PATCH       |  Y   | updated event       |
| ?events/:id    | DELETE      |  Y   | true / false        |

|  URL           | HTTP METHOD | AUTH | JSON Response       |
|----------------|-------------|------|---------------------|
| ?helpdesk      | POST        |  Y   | input -> Gemini     |
