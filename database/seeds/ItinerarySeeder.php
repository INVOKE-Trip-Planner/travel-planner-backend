<?php

use App\Models\Destination;
use App\Models\Itinerary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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
        $date_diff = Destination::selectRaw('datediff(end_date, start_date)')->get()->toArray();
        // $date_diff = Arr::add($date_diff, 'datediff(end_date, start_date)', 3);
        $date_diff = array_column($date_diff, 'datediff(end_date, start_date)');
        // error_log(print_r($date_diff, true));
        $itineraries = [];

        for ($i = 1; $i <= $destination_count; $i ++ ) {
            $diff = $date_diff[$i] ?? $faker->numberBetween($min=1, $max=3);
            foreach(range(1, $diff) as $j) {
                array_push($itineraries, [
                    'destination_id' => $i,
                    'day' =>  $j,
                ]);
            }
        }

        Itinerary::insert($itineraries);
    }
}
