<?php

use App\Models\Accommodation;
use App\Models\Destination;
use App\Models\Itinerary;
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
                    'trip_id' => $i,
                    'location' => $cities[$j],
                ]);

                Transport::create([
                    'destination_id' => $destination->id,
                    'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                    'origin' => $cities[$j - 1],
                    'destination' => $cities[$j],
                    'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                    'booking_id' => $faker->randomNumber($nbDigits = 6),
                ]);

                Accommodation::create([
                    'destination_id' => $destination->id,
                    'accommodation_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                    'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
                    'booking_id' => $faker->bothify('??#####?'),
                ]);

                $schedule = [];

                foreach(range(0, $faker->randomDigit) as $k) {
                    $activity = [
                        'activity' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                        'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                    ];
                    array_push($schedule, $activity);
                }

                Itinerary::create([
                    'destination_id' => $destination->id,
                    'schedule' => $schedule,
                ]);

                if ($j === $n - 1) {
                    Transport::create([
                        'destination_id' => $destination->id,
                        'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                        'origin' => $cities[$j],
                        'destination' => $cities[0],
                        'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                        'booking_id' => $faker->bothify('???###'),
                    ]);
                }
            }


        }
    }
}
