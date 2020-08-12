<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'username' => "test000$i",
                'name' => $faker->name,
                'email' => "test000$i@gmail.com", // $faker->email
                'password' => Hash::make("test000$i"),
            ]);
        }
    }
}
