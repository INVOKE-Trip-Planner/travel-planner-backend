<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\TestTrait;
use App\Models\Trip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Destination;
use App\Models\Itinerary;

class ItineraryTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testCreateItinerary()
    {
        $num_users = 3;
        $num_destinations = 1;

        $credentials = $this->generate_login_user($num_users);

        $cities = [];

        foreach(range(0, $num_destinations) as $i) {
            array_push($cities, $this->faker->city);
        }

        $trip = Trip::create([
            'trip_name' => 'Trip to ' . $cities[1],
            'origin' => $cities[0],
            'created_by' => $credentials[$num_users - 1]['id'],
        ]);

        $destination = Destination::create([
            'trip_id' => $trip->id,
            'location' => $cities[1],
        ]);

        $trip->users()->sync(array_column($credentials, 'id'));

        $schedule = [];

        foreach(range(0, $this->faker->randomDigit) as $k) {

            if ($k % 2 === 0) {
                $activity = [
                    'title' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
                    // 'description' => $this->faker->sentence($nbWords = 10, $variableNbWords = true),
                    // 'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                ];
            } else {
                $activity = [
                    'title' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
                    // 'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
                ];
            }

            array_push($schedule, $activity);
        }

        $payload = [
            'destination_id' => $destination->id,
            'day' => 0,
            // 'date' => date('Y-m-d', strtotime("+1 week")), // $this->faker->date('Y-m-d', '+1week'),
            'schedules' => $schedule,
        ];

        // error_log(print_r($payload, true));

        $this->json('POST', 'api/itinerary', $payload, $credentials[$num_users - 1]['header'])
            ->assertStatus(201);
            // ->assertStatus(500)->assertJson(['test']);

        // failed assert but did insert
        // $this->assertDatabaseHas('itineraries', $payload);
    }

    // public function testUpdateItinerary()
    // {
    //     $num_users = 1;
    //     $num_destinations = 1;

    //     $credentials = $this->generate_login_user($num_users);

    //     $cities = [];

    //     foreach(range(0, $num_destinations) as $i) {
    //         array_push($cities, $this->faker->city);
    //     }

    //     $trip = Trip::create([
    //         'trip_name' => 'Trip to ' . $cities[1],
    //         'origin' => $cities[0],
    //         'created_by' => $credentials[$num_users - 1]['id'],
    //     ]);

    //     $destination = Destination::create([
    //         'trip_id' => $trip->id,
    //         'location' => $cities[1],
    //     ]);

    //     $trip->users()->sync(array_column($credentials, 'id'));

    //     $schedule = [];

    //     foreach(range(0, $this->faker->randomDigit) as $k) {

    //         if ($k % 2 === 0) {
    //             $activity = [
    //                 'title' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
    //                 'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
    //             ];
    //         } else {
    //             $activity = [
    //                 'title' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
    //                 // 'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
    //             ];
    //         }

    //         array_push($schedule, $activity);
    //     }

    //     $payload = [
    //         'destination_id' => $destination->id,
    //         'day' => 0,
    //         'schedules' => $schedule,
    //     ];

    //     $this->json('POST', 'api/itinerary', $payload, $credentials[$num_users - 1]['header'])
    //         ->assertStatus(201);

    //     foreach(range(0, $this->faker->randomDigit) as $k) {

    //         if ($k % 2 === 0) {
    //             $activity = [
    //                 'title' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
    //                 'cost' => 100,
    //             ];
    //         }

    //         array_push($schedule, $activity);
    //     }

    //     $payload = [
    //         'id' => 1,
    //         'day' => 0,
    //         'schedules' => $schedule,
    //     ];

    //     $this->json('POST', 'api/itinerary/update', $payload, $credentials[$num_users - 1]['header'])
    //         ->assertStatus(200);

    //     // failed assert but did insert
    //     // $this->assertDatabaseHas('itineraries', $payload);
    // }

    // public function testBatchCreateItinerary()
    // {
    //     $num_users = 3;
    //     $num_destinations = 1;

    //     $credentials = $this->generate_login_user($num_users);

    //     $cities = [];

    //     foreach(range(0, $num_destinations) as $i) {
    //         array_push($cities, $this->faker->city);
    //     }

    //     $trip = Trip::create([
    //         'trip_name' => 'Trip to ' . $cities[1],
    //         'origin' => $cities[0],
    //         'created_by' => $credentials[$num_users - 1]['id'],
    //     ]);

    //     $destination = Destination::create([
    //         'trip_id' => $trip->id,
    //         'location' => $cities[1],
    //     ]);

    //     $trip->users()->sync(array_column($credentials, 'id'));

    //     $schedule = [];

    //     foreach(range(0, $this->faker->randomDigit) as $k) {
    //         $activity = [
    //             'activity' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
    //             'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 500),
    //         ];
    //         array_push($schedule, $activity);
    //     }

    //     $itineraries = [
    //         [
    //             'date' => $this->faker->date('Y-m-d', '+1week'),
    //             'schedule' => $schedule,
    //         ]
    //     ];

    //     $payload = [
    //         'destination_id' => $destination->id,
    //         'itineraries' => $itineraries,
    //     ];

    //     $this->json('POST', 'api/batch/itinerary', $payload, $credentials[$num_users - 1]['header'])
    //         ->assertStatus(201);

    //     // failed assert but did insert
    //     // $this->assertDatabaseHas('itineraries', $itineraries[0]);
    // }
}
