## API summaries
1. GET /api/trip to get all trips belong to user.
2. POST /api/trip to create new trip.
    * trip_name, origin, start_date, end_date, group_type, trip_type, users[] (array of user's id), destinations[] (array of objects with keys { location, start_date, end_date }), trip_banner (photo) 
3. POST /api/trip/update to update details of a trip as above EXCEPT location
    * id* (trip id), trip_name, origin, start_date, end_date, group_type, trip_type, users[] (array of user's id), trip_banner (photo) 
4. POST /api/trip/delete to delete trip
    * id* (trip id)

5. POST /api/destination to add destination to trip already created
    * trip_id*, location, start_date, end_date
6. POST /api/destination/update to update destination details
    * id* (destination id), location, start_date, end_date
7. POST /api/destination/delete to delete destination
    * id* (destination id)

8. POST /api/accommodation to add 1 accommodation to a destination 
    * destination_id*, accommodation_name, checkin_time, checkout_time, booking_id
9. POST /api/accommodation/update to update an accommodation
    * id* (accommodation id), accommodation_name, checkin_time, checkout_time, booking_id
10. POST /api/accommodation/delete to delete an accommodation  
    * id* (accommodation id)

11. POST /api/transport to add 1 transport to a destination
    * destination_id*, mode* (FLIGHT, FERRY, BUS, TRAIN, OTHER), origin*, destination*, departure_time, arrival_time, operator, booking_id
12. POST /api/transport/update to update a transport 
    * id* (transport id), mode* (FLIGHT, FERRY, BUS, TRAIN, OTHER), origin*, destination*, departure_time, arrival_time, operator, booking_id
13. POST /api/transport/delete to delete a transport 
    * id* (transport id)

14. POST /api/itinerary to add itinerary for 1 day to a destination 
    * destination_id, date, schedule[]* (array of objects with keys { activity }*)
15. POST /api/itinerary/update to update an itinerary 
    * id (itinerary id), date, schedule[] (array of objects with keys { activity }*)
16. POST /api/itinerary/delete to delete an itinerary
    * id (itinerary id)


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
