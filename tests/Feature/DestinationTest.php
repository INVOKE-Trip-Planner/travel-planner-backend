<?php

namespace Tests\Feature;

use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

// This delete only the data created during tests
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DestinationTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testCreateTrip()
    {
        $num_users = 3;
        $num_destinations = 3;

        $credentials = $this->generate_login_user($num_users);

        $cities = [];
        $destinations = [];

        foreach(range(0, $num_destinations) as $i) {

            $city = $this->faker->city;
            array_push($cities, $city);

            if ($i > 0) {
                $transports = [];

                array_push($transports,
                    [
                        'mode' => $this->faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                        'origin' => $cities[$i - 1],
                        'destination' => $cities[$i],
                        'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                        'booking_id' => $this->faker->randomNumber($nbDigits = 6),
                    ]
                );

                if ($i === $num_destinations) {
                    array_push($transports,
                        [
                            'mode' => $this->faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                            'origin' => $cities[$i],
                            'destination' => $cities[0],
                            'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                            'booking_id' => $this->faker->randomNumber($nbDigits = 6),
                        ]
                    );
                }

                array_push($destinations,
                    [
                        'location' => $city,
                        'transports' => $transports,
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
