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
        $faker->seed(crc32('planner'));

        $users = [];

        for ($i = 1; $i <= 10; $i++) {
            $index =  str_pad($i, 4, '0', STR_PAD_LEFT);
            array_push($users, [
                'username' => "test$index",
                'name' => $faker->name,
                'email' => "test$index@gmail.com", // $faker->email
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password // Hash::make("test$index"),
                'avatar' => "test$index.jpg"
            ]);
        }

        User::insert($users);
    }
}
