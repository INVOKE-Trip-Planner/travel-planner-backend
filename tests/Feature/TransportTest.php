<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\TestTrait;
use App\Models\Trip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Destination;

class TransportTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testBatchCreateTransport()
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

        $transports = [
            [
                'destination_id' => $destination->id,
                'mode' => $this->faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                'origin' => $cities[0],
                'destination' => $cities[1],
                'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                'booking_id' => $this->faker->randomNumber($nbDigits = 6),
            ],
            [
                'destination_id' => $destination->id,
                'mode' => $this->faker->randomElement($array = ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']),
                'origin' => $cities[1],
                'destination' => $cities[0],
                'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 2000),
                'booking_id' => $this->faker->randomNumber($nbDigits = 6),
            ],
        ];

        $payload = [
            'destination_id' => $destination->id,
            'transports' => $transports,
        ];

        $this->json('POST', 'api/batch/transport', $payload, $credentials[$num_users - 1]['header'])
            ->assertStatus(201);

        $this->assertDatabaseHas('transports', $transports[0]);
    }
}
