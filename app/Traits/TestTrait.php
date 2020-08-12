<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Task;
use Illuminate\Support\Facades\Hash;

trait TestTrait
{

    private function generate_user($num_user, $return_id_only=False)
    {
        $credentials = [];
        $id = [];

        for ($i = 0; $i < $num_user; $i++) {
            $credential = [
                'username' => "username$i",
                'password' => "password",
            ];

            $user = factory(User::class)->create([
                'username' => $credential['username'],
                'password' => Hash::make($credential['password']),
            ]);

            $credential['id'] = $user->id;

            array_push($id, $user->id);
            array_push($credentials, $credential);
        }

        if ($return_id_only)
        {
            return $id;
        }

        return $credentials;
    }

    /**
     * failed when trying to login more than 1 user
     */
    private function login_user($credentials)
    {
        $this->withoutMiddleware();

        $tokens = [];

        foreach($credentials as $c) {
            $token = $this->json('POST', 'api/login', [
                'username' => $c['username'],
                'password' => 'password',
            ])['token'];

            array_push($tokens, $token);
        }

        return $tokens;
    }

    private function generate_login_user($num_user) {
        $credentials = $this->generate_user($num_user);
        $tokens = $this->login_user($credentials);

        for ($i = 0; $i < $num_user; $i++) {
            $credentials[$i]['token'] = $tokens[$i];
            $credentials[$i]['header'] = ['Authorization' => "Bearer {$tokens[$i]}"];
        }

        return $credentials;
    }

    // private function create_tasks($num_task, $user_id) {
    //     $faker = \Faker\Factory::create();
    //     $tasks = [];

    //     foreach($user_id as $u) {
    //         $tasks_per_user = [];

    //         for ($i = 0; $i < $num_task; $i++) {
    //             $task = Task::create([
    //                 'task_name' => $faker->sentence,
    //                 'user_id' => $u,
    //             ]);
    //             array_push($tasks_per_user, $task->id);
    //         }

    //         $tasks[$u] = $tasks_per_user;
    //     }

    //     return $tasks;
    // }
}
