## Set up
1. Edit .env
    * Change DB_DATABASE if desire
    * Add L5_SWAGGER_GENERATE_ALWAYS=true
2. Generate JWT secret by running php artisan jwt:secret
3. Create database if not exists in workbench
4. Migrate tables by running php artisan migrate:refresh
5. Generate fake data by running php artisan db:seed 
    * This will generate 10 users with username from 'test0000' til 'test0009' and password same as the username.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
