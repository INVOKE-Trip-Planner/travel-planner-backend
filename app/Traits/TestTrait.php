<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;

trait TestTrait
{
    use WithoutMiddleware;

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

}
