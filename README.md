# Vanilla PHP REST API - UCC project

## Endpoints (végpontok)

|  URL                  | HTTP METHOD | AUTH | JSON Response       |
|-----------------------|-------------|------|---------------------|
| ?user=login           | POST        |      | user's token        |
| ?users=register       | POST        |      | new user            |
| ?users=reset-password | POST        |      | new user            |
| ?users=new-password   | POST        |      | new user            |

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



# **Plain PHP REST API – Eseménykezelő rendszer**

Ez egy egyszerű REST API, amit Vanilla PHP és MySQL segítségével készítettem el. Egy eseménykezelő rendszer létrehozása volt a célom, amelyen keresztül a felhasználók eseményeket hozhatnak létre, listázhatják, szerkeszthetik és törölhetik azokat. 
------

## ** Felhasznált technológiák**  
    
    -Backend: PHP (Vanilla, tehát nincs Laravel vagy más framework)  

    -Adatbázis: MySQL (MySQL. phpMyAdmin, XAMPP-on)  

    -Autentikáció: JWT (JSON Web Token)  

    -Biztonság: HTTPS, SQL Injection védelem, Token alapú hitelesítés  

--- 
