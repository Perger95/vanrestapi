# Vanilla PHP REST API

## Endpoints (végpontok)

|  URL           | HTTP METHOD | AUTH | JSON Response       |
|----------------|-------------|------|---------------------|
| ?user=login    | POST        |      | user's token        |
| ?users         | GET         |  Y   | all users           |
| ?products      | GET         |      | all products        |
| ?products      | POST        |  Y   | new product added   |
| ?products      | PATCH       |  Y   | edited product      |
| ?products      | DELETE      |  Y   | true / false        |
| ?events        | GET         |  Y   | user's events       |
| ?events        | POST        |  Y   | new event added     |
| ?events/:id    | PATCH       |  Y   | updated event       |
| ?events/:id    | DELETE      |  Y   | true / false        |