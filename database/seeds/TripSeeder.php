<?php

use App\Models\Cost;
use App\Models\Destination;
use App\Models\Transport;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $transports = [];

        for ($i = 1; $i <= 20; $i++) {

            $faker->seed($i);

            $creator = $faker->randomDigitNotNull();
            $n = $faker->numberBetween($min=2, $max=4);
            $cities = [];

            for ($j = 0; $j < $n; $j++) {
                array_push($cities, $faker->city);

                if ($j == 0) {
                    continue;
                } else if ($j == 1) {
                    $trip = Trip::create([
                        'trip_name' => 'Trip to ' . $cities[1],
                        'origin' => $cities[0],
                        'created_by' => $creator,
                    ]);

                    $trip->users()->sync(array_merge($faker->randomElements($array = range(1, 10), $count = $faker->numberBetween($min=0, $max=4)), [$creator]));
                }

                $destination = Destination::create([
                    'trip_id' => $trip->id,
                    'location' => $cities[$j],
                ]);

                // transport to destination
                array_push($transports, [
                    'destination_id' => $destination->id,
                    'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                    'origin' => $cities[$j - 1],
                    'destination' => $cities[$j],
                    'booking_id' => $faker->randomNumber($nbDigits = 6),
                ]);

                // transport back to origin
                if ($j === $n - 1) {
                    array_push($transports, [
                        'destination_id' => $destination->id,
                        'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                        'origin' => $cities[$j],
                        'destination' => $cities[0],
                        'booking_id' => $faker->bothify('???###'),
                    ]);
                }
            }


        }

        Transport::insert($transports);

        $transport_count = Transport::all()->count();
        $costs = [];

        for ($i = 1; $i <= $transport_count; $i++) {
            array_push($costs, [
                    'costable_id' => $i,
                    'costable_type' => 'App\Models\Transport',
                    'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                ]
            );
        }

        Cost::insert($costs);
    }
}
