<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;
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
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="numeric | digits_between:10,11 | unique:users",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="in:MALE,FEMALE,OTHER",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="birth_date",
     *         in="query",
     *         description="date_format:Y-m-d | before:today",
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
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     ),
     * )
     */
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'username' => ['string', 'max:25', 'unique:users'],
            'name' => ['string', 'max:255'],
            'email' => ['email:rfc,dns', 'max:255', 'unique:users'],
            'phone' => ['numeric', 'digits_between:10,11', 'unique:users'],
            'password' => ['string', 'min:8', 'confirmed'],
            'avatar' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'gender' => ['in:MALE,FEMALE,OTHER'],
            'birth_date' => ['date_format:Y-m-d', 'before:today'],
        ])->validate();

        $data = $request->except(['password', 'avatar']);

        $user = Auth::user();

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->has('avatar')) {
            $avatarName = $user->id . '_avatar' . time() . '.' .request()->avatar->getClientOriginalExtension();
            $request->avatar->storeAs('avatars', $avatarName, 'public');
            $data['avatar'] = $avatarName;
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/search/{query}",
     *     tags={"User"},
     *     summary="Search user",
     *     description="Search users table username, name & email columns",
     *     operationId="user_by_query",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *          @OA\Schema(type="string"),
     *          in="path",
     *          allowReserved=true,
     *          name="query",
     *          parameter="query"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function search(Request $request, $query) {

        $users = User::select('id', 'name', 'avatar')->search($query)->get();

        return response()->json($users, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/find/{id}",
     *     tags={"User"},
     *     summary="Find user",
     *     description="Get user by id",
     *     operationId="user_by_id",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *          @OA\Schema(type="string"),
     *          in="path",
     *          allowReserved=true,
     *          name="id",
     *          parameter="id"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function findById(Request $request, $id) {

        $user = User::findOrFail($id)->only('id', 'avatar', 'name');

        return response()->json($user, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user/checkavailability",
     *     tags={"User"},
     *     summary="Find user",
     *     description="Check username or email availability",
     *     operationId="username_email_availability",
     *     deprecated=true,
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
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function checkUsernameEmailAvailability(Request $request) {

        Validator::make($request->all(), [
            'username' => 'required_without:email|string|exists:users',
            'email' => 'required_without:username|email|exists:users',
        ], [
            'exists' => 'The :attribute is available.',
            'exists' => 'The :attribute is available.',
        ])->validate();

        if ($request->has('username')) {
            $response = ['message' => 'Username is already taken.'];
        } else if ($request->has('email')) {
            $response = ['message' => 'Email is already taken.'];
        }

        return response()->json($response, 200);
    }

}
