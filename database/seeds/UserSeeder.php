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

        $users = [];

        for ($i = 0; $i < 10; $i++) {
            $index =  str_pad($i, 4, '0', STR_PAD_LEFT);
            array_push($users, [
                'username' => "test$index",
                'name' => $faker->name,
                'email' => "test$index@gmail.com", // $faker->email
                'password' => Hash::make("test$index"),
            ]);
        }

        User::insert($users);
    }
}
