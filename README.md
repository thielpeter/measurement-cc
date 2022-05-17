# movingimage Coding Challenge

This is the solution for the movingimage Coding Challenge provided by Peter Thiel.

## Installation

For installation follow these steps:

1. Run "composer install"
2. Set "APP_URL=//{domain}" in .env
3. Add "L5_SWAGGER_CONST_HOST=//{domain}/api/v1" to .env
4. RUN php artisan l5-swagger:generate

## Evaluation

For evaluation use:

- Swagger API at //{domain}/api/documentation
- Postman Collection "MovingImage CC.postman_collection.json" attached
- Run Tests with "php artisan test"

Looking forward to your feedback at thiel.peter@gmail.com
