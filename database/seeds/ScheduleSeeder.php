<?php

use App\Models\Cost;
use App\Models\Itinerary;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $itinerary_count = Itinerary::all()->count();
        $schedules = [];

        for ($i = 1; $i <= $itinerary_count; $i++) {
            foreach(range(0, $faker->randomDigit) as $j) {

                array_push($schedules,
                    [
                        'itinerary_id' => $i,
                        'title' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                        'hour' => $faker->numberBetween($min=0, $max=23),
                        'minute' => $faker->numberBetween($min=0, $max=59),
                        // 'description' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                        // 'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                    ]
                );
            }
        }

        Schedule::insert($schedules);

        $schedule_count = Schedule::all()->count();
        $costs = [];

        for ($i = 1; $i <= $schedule_count; $i++) {
            array_push($costs, [
                    'costable_id' => $i,
                    'costable_type' => 'App\Models\Schedule',
                    'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                ]
            );
        }

        Cost::insert($costs);
    }
}
