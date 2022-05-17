# movingimage Coding Challenge

This is the solution for the movingimage Coding Challenge provided by Peter Thiel.

## Installation

For installation follow these steps:

1. Run `composer install`
2. Copy .env.example to .env
3. Set `APP_URL=//{domain}` in .env
4. Set database config in .env (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
5. Add `L5_SWAGGER_CONST_HOST=//{domain}/api/v1` to .env
6. Run `php artisan l5-swagger:generate`
7. Run `php artisan migrate`
8. Run `php artisan optimize`

## Evaluation

For evaluation use:

- Swagger API at `//{domain}/api/documentation`
- Postman Collection "MovingImage CC.postman_collection.json" attached
- Run Tests with `php artisan test`

Looking forward to your feedback at thiel.peter@gmail.com

## Notes
- As required the endpoint `POST /sensors/{uuid}/measurements` is throttled to 1 request per minute.
- Make sure folders have correct permissions (if possible use `chmod -R 777 ./`)
