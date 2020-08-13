<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\TestTrait;
use App\Models\Trip;
use App\Models\Destination;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AccommodationTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testCreateAccommodation()
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

        $accommodations = [
            [
                'accommodation_name' => $this->faker->company . ' ' . $this->faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                'cost' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
                'booking_id' => $this->faker->bothify('??#####?'),
            ]
        ];

        $payload = [
            'destination_id' => $destination->id,
            'accommodations' => $accommodations,
        ];

        $this->json('POST', 'api/accommodation', $payload, $credentials[$num_users - 1]['header'])
            ->assertStatus(201);

        $this->assertDatabaseHas('accommodations', $accommodations[0]);
    }
}
