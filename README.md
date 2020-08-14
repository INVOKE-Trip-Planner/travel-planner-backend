## Set up
1. composer update --no-scripts
2. Rename .env.example to .env & edit the following:
    * Change DB_DATABASE to your local database name
    * Add L5_SWAGGER_GENERATE_ALWAYS=true
3. php artisan key:generate
4. Generate JWT secret by running php artisan jwt:secret
5. Create database if not exists in workbench
6. Migrate tables by running php artisan migrate:refresh
7. Generate fake data by running php artisan db:seed 
    * This will generate 10 users with username from 'test0000' til 'test0009' and password same as the username.
    
php artisan l5-swagger:generate


## Misc
1. Documentation for APIs can be found at /api/documentation.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
