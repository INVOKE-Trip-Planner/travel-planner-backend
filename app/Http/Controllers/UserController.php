<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    //
    /**
     * @OA\Post(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Update user info",
     *     description="Update user info",
     *     operationId="update",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="avatar",
     *                      type="file",
     *                      description="[image | mimes:jpeg, png, jpg, gif, svg | max:2048",
     *                      @OA\Items(type="string", format="binary")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="string | max:255",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="string | max:25 | unique:users",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="email:rfc,dns | max:255 | unique:users",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="string | min:8 | confirmed",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="required_with:password",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to generate token"
     *     ),
     * )
     */
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'username' => ['string', 'max:25', 'unique:users'],
            'name' => ['string', 'max:255'],
            'email' => ['email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['string', 'min:8', 'confirmed'],
            'avatar' => ['image', 'mimes:jpeg, png, jpg, gif, svg', 'max:2048'],
        ])->validate();

        $data = $request->all();

        $user = Auth::user();

        if ($request->has('avatar')) {
            $avatarName = $user->id . '_avatar' . time() . '.' .request()->avatar->getClientOriginalExtension();
            $request->avatar->storeAs('avatars', $avatarName);
            $data['avatar'] = $avatarName;
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"User"},
     *     summary="Find user",
     *     description="Get user by username or email",
     *     operationId="user_by_username_email",
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="required_without:email | string | exists:users",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="required_without:username | email | exists:users",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function find(Request $request) {

        Validator::make($request->all(), [
            'username' => 'required_without:email|string|exists:users',
            'email' => 'required_without:username|email|exists:users',
        ])->validate();

        if ($request->has('username')) {
            $user = User::where('username', request('username'))->get();
        } else if ($request->has('email')) {
            $user = User::where('email', request('email'))->get();
        }

        error_log('pass validation');

        return response()->json($user, 200);
        // return response()->json('Please pass in username or email.', 422);
    }
}
