## Set up
1. Rename .env.example to .env & edit the following:
    * Change DB_DATABASE if desire
    * Add L5_SWAGGER_GENERATE_ALWAYS=true
2. Generate JWT secret by running php artisan jwt:secret
3. Create database if not exists in workbench
4. Migrate tables by running php artisan migrate:refresh
5. Generate fake data by running php artisan db:seed 
    * This will generate 10 users with username from 'test0000' til 'test0009' and password same as the username.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
