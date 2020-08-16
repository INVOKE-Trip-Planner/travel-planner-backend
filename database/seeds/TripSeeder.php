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

        // $transports = [];
        // $accommodations = [];
        // $itineraries = [];

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

                // array_push($transports, [
                //     'destination_id' => $destination->id,
                //     'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                //     'origin' => $cities[$j - 1],
                //     'destination' => $cities[$j],
                //     // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                //     'booking_id' => $faker->randomNumber($nbDigits = 6),
                // ]);

                $transport = Transport::create([
                    'destination_id' => $destination->id,
                    'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                    'origin' => $cities[$j - 1],
                    'destination' => $cities[$j],
                    // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                    'booking_id' => $faker->randomNumber($nbDigits = 6),
                ]);

                $transport->cost()->create(['cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000)]);

                // array_push($accommodations, [
                //     'destination_id' => $destination->id,
                //     'accommodation_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                //     // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
                //     'booking_id' => $faker->bothify('??#####?'),
                // ]);

                $accommodation = Accommodation::create([
                    'destination_id' => $destination->id,
                    'accommodation_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                    // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
                    'booking_id' => $faker->bothify('??#####?'),
                ]);

                $accommodation->cost()->create(['cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000)]);

                $schedule = [];

                foreach(range(0, $faker->randomDigit) as $k) {
                    $activity = [
                        'activity' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                        // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                    ];
                    array_push($schedule, $activity);
                }

                // array_push($itineraries, [
                //     'destination_id' => $destination->id,
                //     'date' =>  date('Y-m-d', strtotime("+1 week")),
                //     'schedule' => $schedule,
                // ]);

                $itinerary = Itinerary::create([
                    'destination_id' => $destination->id,
                    'date' =>  date('Y-m-d', strtotime("+1 week")),
                    'schedule' => $schedule,
                ]);

                $itinerary->cost()->create(['cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000)]);


                if ($j === $n - 1) {

                    // array_push($transports, [
                    //     'destination_id' => $destination->id,
                    //     'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                    //     'origin' => $cities[$j],
                    //     'destination' => $cities[0],
                    //     // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                    //     'booking_id' => $faker->bothify('???###'),
                    // ]);

                    $transport = Transport::create([
                        'destination_id' => $destination->id,
                        'mode' => $faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                        'origin' => $cities[$j],
                        'destination' => $cities[0],
                        // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                        'booking_id' => $faker->bothify('???###'),
                    ]);

                    $transport->cost()->create(['cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000)]);

                }
            }


        }

        // Transport::insert($transports);
        // Accommodation::insert($accommodations);
        // Itinerary::insert($itineraries);
    }
}
