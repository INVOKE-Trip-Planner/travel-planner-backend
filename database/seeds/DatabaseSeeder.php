<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(TripSeeder::class);
        $this->call(DestinationSeeder::class);
        $this->call(TransportSeeder::class);
        $this->call(AccommodationSeeder::class);
        $this->call(ItinerarySeeder::class);
        $this->call(ScheduleSeeder::class);
    }
}
