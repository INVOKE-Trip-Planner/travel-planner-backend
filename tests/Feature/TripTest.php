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
    use DatabaseTransactions, TestTrait, WithFaker;

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

            $city = $this->faker->city;
            array_push($cities, $city);

            if ($i > 0) {
                array_push($destinations,
                    [
                        'location' => $city,
                    ]
                );
            }
        }

        $payload = [
            'trip_name' => 'Trip to ' . $this->faker->city,
            'origin' => $this->faker->city,
            'users' => array_column($credentials, 'id'),
            'destinations' => $destinations,
        ];

        $this->json('POST', 'api/trip', $payload, $credentials[0]['header'])
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'trip_name',
                'origin',
                'created_by',
                'start_date',
                'end_date',
                'cost',
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
                        'cost',
                        'transports' => [],
                        'accommodations' => [],
                        'itineraries' => [],
                    ]
                ],
            ]);
    }
}
