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

        $day_tracker = -30;

        $destinations = [];
        $destination_counter = 1;

        $transports = [];

        for ($i = 1; $i <= 20; $i++) {

            $faker->seed($i);
            $has_dates = $faker->boolean($chanceOfGettingTrue = 70);

            $creator = $faker->randomDigitNotNull();
            $n = $faker->numberBetween($min=2, $max=5);
            $cities = [];

            for ($j = 0; $j < $n; $j++) {
                array_push($cities, $faker->city);

                if ($j == 0) {
                    continue;
                } else if ($j == 1) {
                    $trip = Trip::create([
                        // 'trip_name' => 'Trip to ' . $cities[1],
                        'origin' => $cities[0],
                        'created_by' => $creator,
                    ]);

                    $trip->users()->sync(array_merge($faker->randomElements($array = range(1, 10), $count = $faker->numberBetween($min=0, $max=4)), [$creator]));
                }

                if ($has_dates) {
                    $start_date = date('Y-m-d', strtotime("+$day_tracker day"));
                    $num_days = $faker->numberBetween($min=1, $max=4);
                    $day_tracker += $num_days;
                    $end_date = date('Y-m-d', strtotime("+$day_tracker day"));
                }

                array_push($destinations,[
                    'trip_id' => $i,
                    'location' => $cities[$j],
                    'start_date' => $has_dates ? $start_date : null,
                    'end_date' => $has_dates ? $end_date: null,
                ]);

                // transport to destination
                array_push($transports, [
                    'destination_id' => $destination_counter,
                    'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                    'origin' => $cities[$j - 1],
                    'destination' => $cities[$j],
                    'booking_id' => $faker->bothify('???###'), // $faker->randomNumber($nbDigits = 6),
                    'departure_date' => $has_dates ? $start_date : null,
                    'arrival_date' => $has_dates ? $start_date: null,
                ]);

                // transport back to origin
                if ($j === $n - 1) {
                    array_push($transports, [
                        'destination_id' => $destination_counter,
                        'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                        'origin' => $cities[$j],
                        'destination' => $cities[0],
                        'booking_id' => $faker->bothify('???###'),
                        'departure_date' => $has_dates ? $end_date : null,
                        'arrival_date' => $has_dates ? $end_date: null,
                    ]);
                }

                $destination_counter++;
            }

            $day_tracker += 10;
        }

        Destination::insert($destinations);
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
