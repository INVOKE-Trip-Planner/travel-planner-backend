<?php

namespace Tests\Feature;

// This refresh the whole database
// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

// This delete only the data created during tests
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TripTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testCreateTrip()
    {
        // $users = factory(App\User::class, 3)->make();
        // $user = factory(User::class)->make();
        $num_users = 3;
        $num_destinations = 3;

        $credentials = $this->generate_login_user($num_users);

        $cities = [];
        $destinations = [];

        foreach(range(0, $num_destinations) as $i) {

            array_push($cities, $this->faker->city);

            if ($i > 0) {

                array_push($destinations,
                    [
                        'location' => $cities[$i],
                    ]
                );
            }
        }

        $payload = [
            'trip_name' => 'Trip to ' . $cities[1],
            'origin' => $cities[0],
            'users' => array_column($credentials, 'id'),
            'destinations' => $destinations,
        ];

        // error_log(print_r($destinations, true));
        // error_log(print_r($payload, true));

        $this->json('POST', 'api/trip', $payload, $credentials[0]['header'])
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'trip_name',
                'origin',
                'created_by',
                'start_date',
                'end_date',
                // 'cost',
                'trip_banner',
                'users' => [
                    '*' => [
                        'id',
                        'avatar',
                    ],
                ],
                'destinations' => [
                    '*' => [
                        'id',
                        'trip_id',
                        'location',
                        'start_date',
                        'end_date',
                        // 'cost',
                        'transports' => [],
                        'accommodations' => [],
                        'itineraries' => [],
                    ]
                ],
            ]);
    }
}
