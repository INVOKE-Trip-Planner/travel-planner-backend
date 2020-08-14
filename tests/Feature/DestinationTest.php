<?php

namespace Tests\Feature;

use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Trip;
use App\Models\Destination;

// This delete only the data created during tests
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DestinationTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

    public function testGetDestination()
    {
        $credentials = $this->generate_login_user(1);

        $num_destinations = 1;

        $cities = [];

        foreach(range(0, $num_destinations) as $i) {
            array_push($cities, $this->faker->city);
        }

        $trip = Trip::create([
            'trip_name' => 'Trip to ' . $cities[1],
            'origin' => $cities[0],
            'created_by' => $credentials[0]['id'],
        ]);

        $destination = Destination::create([
            'trip_id' => $trip->id,
            'location' => $cities[1],
        ]);

        $this->json('GET', 'api/destination', [], $credentials[0]['header'])
        ->assertStatus(200);
    }
}
