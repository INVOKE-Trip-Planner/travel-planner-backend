<?php

use App\Models\Destination;
use App\Models\Itinerary;
use Illuminate\Database\Seeder;

class ItinerarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $destination_count = Destination::all()->count();
        $itineraries = [];

        for ($i = 1; $i <= $destination_count; $i ++ ) {
            foreach(range(1, $faker->numberBetween($min=1, $max=3)) as $j) {
                array_push($itineraries, [
                    'destination_id' => $i,
                    'day' =>  $j,
                ]);
            }
        }

        Itinerary::insert($itineraries);
    }
}
