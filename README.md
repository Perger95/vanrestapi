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
