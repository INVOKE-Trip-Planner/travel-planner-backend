<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"User"},
     *     summary="Register user",
     *     description="Register user",
     *     operationId="register_user",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="avatar",
     *                      type="file",
     *                      description="image | mimes:jpeg, png, jpg, gif, svg | max:2048",
     *                      @OA\Items(type="string", format="binary")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="required | string | max:255",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="required | string | max:25 | unique:users",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="required | email:rfc,dns | max:255 | unique:users",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="required | string | min:8 | confirmed",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="required",
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
     *         response=201,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     ),
     * )
     */
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $start_time = microtime(true);

        Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:25', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required' ,'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['numeric', 'digits_between:10,11', 'unique:users'],
            'avatar' => ['image', 'mimes:jpeg, png, jpg, gif, svg', 'max:2048'],
            'gender' => ['in:MALE,FEMALE,OTHER'],
            'birth_date' => ['date_format:Y-m-d', 'before:today'],
        ])->validate();

        $data = $request->except('avatar');

        $data['last_login_ip'] = $request->getClientIp();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->has('avatar')) {
            $avatarName = $user->id . '_avatar' . time() . '.' .request()->avatar->getClientOriginalExtension();
            $request->avatar->storeAs('avatars', $avatarName);
            $user->avatar = $avatarName;
            $user->save();
        } // else {
        //     $user->avatar = 'placeholder.png';
        // }

        $token = $this->guard()->login($user);
        // $token = JWTAuth::attempt($request->only(['username', 'password']));
        // $user = User::find($user->id);
        $user = $user->fresh();

        $execution_time = microtime(true) - $start_time;
        error_log("Execution time of register = $execution_time");

        return response()->json(compact('token', 'user'), 201);
    }


}
