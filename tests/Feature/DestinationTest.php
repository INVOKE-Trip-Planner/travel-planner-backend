<?php

namespace Tests\Feature;

use App\Traits\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Trip;

// This delete only the data created during tests
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DestinationTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait, WithFaker;

}
