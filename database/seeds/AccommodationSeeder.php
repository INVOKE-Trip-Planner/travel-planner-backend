<?php

use App\Models\Accommodation;
use App\Models\Cost;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $destinations = Destination::all()->toArray();
        $accommodations = [];

        foreach($destinations as $destination) {
            foreach(range(1, $faker->numberBetween($min=1, $max=3)) as $j) {
                array_push($accommodations, [
                    'destination_id' => $destination['id'],
                    'accommodation_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                    'booking_id' => $faker->bothify('??#####?'),
                    'checkin_date' => $destination['start_date'],
                    'checkout_date' => $destination['end_date'],
                ]);
            }
        }

        Accommodation::insert($accommodations);

        $accommodation_count = Accommodation::all()->count();
        $costs = [];

        for ($i = 1; $i <= $accommodation_count; $i++) {
            array_push($costs, [
                    'costable_id' => $i,
                    'costable_type' => 'App\Models\Accommodation',
                    'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
                ]
            );
        }

        Cost::insert($costs);
    }
}
