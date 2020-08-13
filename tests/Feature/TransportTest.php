<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\TestTrait;
use App\Models\Trip;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TransportTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testCreateTransport()
    {
        $num_users = 3;
        $num_destinations = 1;

        $credentials = $this->generate_login_user($num_users);

        $cities = [];
        $destinations = [];

        foreach(range(0, $num_destinations) as $i) {
            array_push($cities, $this->faker->city);

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
                        'location' => $cities[$i],
                        'transports' => $transports,
                    ]
                );
            }
        }

        $trip = Trip::create([
            'trip_name' => 'Trip to ' . $cities[1],
            'origin' => $cities[0],
            'created_by' => $credentials[0]['id'],
        ]);

        $trip->users()->sync(array_column($credentials, 'id'));

        $trip->destinations()->createMany($destinations);

        $payload = [
            'destination_id' => $trip->destinations()->first()['id'],
            'transport' => $destinations[0]['transports'],
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
