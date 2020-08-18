<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class AccommodationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/accommodation",
     *     tags={"Accommodation"},
     *     summary="Get all accommodations for logged in user",
     *     description="Get all accommodations for logged in user",
     *     operationId="get_accommodation",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
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
    function get(Request $request)
    {
        $accommodations = Auth::user()->accommodations()->get();

        return response()->json($accommodations, 200);
    }

   /**
     * @OA\Post(
     *     path="/api/accommodation",
     *     tags={"Accommodation"},
     *     summary="Create a accommodation for a destination",
     *     description="Create accommodation",
     *     operationId="create_accommodation",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="destination_id",
     *         in="query",
     *         description="required | exists:destinations,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="accommodation_name",
     *         in="query",
     *         description="required_with:accommodations | string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:checkin_date",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_minute",
     *         in="query",
     *         description="nnumeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cost",
     *         in="query",
     *         description="numeric | min:0",
     *         @OA\Schema(
     *             type="decimal"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="booking_id",
     *         in="query",
     *         description="string | max:20",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
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
    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'destination_id' => 'required|exists:destinations,id',
            'accommodation_name' => 'required|string|max:100',
            'checkin_date' => 'date_format:Y-m-d|after:today',
            'checkout_date' => 'date_format:Y-m-d|after:checkin_date',
            'checkin_hour' => 'numeric|min:0|max:23',
            'checkout_hour' => 'numeric|min:0|max:23',
            'checkin_minute' => 'numeric|min:0|max:59',
            'checkout_minute' => 'numeric|min:0|max:59',
            'cost'=> 'numeric|min:0',
            'booking_id'=> 'string|max:20',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        // error_log(print_r($request->except(['destination_id', 'cost']), true));
        // error_log(print_r($destination->accommodations()->create($request->except(['destination_id', 'cost']))->toSql()));

        $accommodation = $destination->accommodations()->create($request->except(['destination_id', 'cost']));
        if ($request->has('cost')) {
            // error_log(print_r($request->only('cost'), true));
            $accommodation->cost()->create($request->only('cost'));
        }

        return response()->json($accommodation, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/accommodation/update",
     *     tags={"Accommodation"},
     *     summary="Update accommodation",
     *     description="Update accommodation",
     *     operationId="update_accommodation",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:accommodations,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="accommodation_name",
     *         in="query",
     *         description="required_with:accommodations | string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:checkin_date",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkin_minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkout_minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cost",
     *         in="query",
     *         description="numeric | min:0",
     *         @OA\Schema(
     *             type="decimal"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="booking_id",
     *         in="query",
     *         description="string | max:20",
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
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|exists:accommodations,id',
            'accommodation_name' => 'string|max:100',
            'checkin_date' => 'date_format:Y-m-d|after:today',
            'checkout_date' => 'date_format:Y-m-d|after:checkin_date',
            'checkin_hour' => 'numeric|min:0|max:23',
            'checkout_hour' => 'numeric|min:0|max:23',
            'checkin_minute' => 'numeric|min:0|max:59',
            'checkout_minute' => 'numeric|min:0|max:59',
            'cost'=> 'numeric|min:0',
            'booking_id'=> 'string|max:20',
        ])->validate();

        $accommodation = Accommodation::findOrFail($request->id);
        // $destination = $accommodation->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        // error_log($accommodation->users()->toSql());
        // return response()->json($accommodation->users()->get(), 200);

        if ($accommodation->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $accommodation->update($request->except('cost'));

        if ($request->has('cost')) {
            if ($accommodation->cost) {
                $accommodation->cost()->update($request->only('cost'));
            } else {
                $accommodation->cost()->create($request->only('cost'));
            }
        }

        // to get updated values
        $accommodation = Accommodation::findOrFail($request->id);

        return response()->json($accommodation, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/accommodation/delete",
     *     tags={"Accommodation"},
     *     summary="Delete accommodation",
     *     description="Delete accommodation",
     *     operationId="delete_accommodation",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:accommodations,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
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
    public function delete(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|exists:accommodations,id',
        ])->validate();

        $accommodation = Accommodation::findOrFail($request->id);
        // $destination = $accommodation->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($accommodation->users()->find(Auth::id()) === null) {

            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $accommodation->delete($request->id);

        $response = ['message' => 'The accommodation has been successfully deleted.'];

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/batch/accommodation",
     *     tags={"Accommodation"},
     *     summary="Create multiple accommodation for a destination",
     *     description="Create accommodation",
     *     operationId="batch_create_accommodation",
     *     security={{"bearerAuth":{}}},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="destination_id",
     *         in="query",
     *         description="required | exists:destinations,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *           name="body",
     *           in="query",
     *           required=false,
     *           explode=true,
     *           @OA\Schema(
     *               @OA\Property(property="accommodation",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="accommodation_name",  type="string",  ),
     *                                @OA\Property(property="checkin_time",  type="string",  ),
     *                                @OA\Property(property="checkout_time",  type="string",  ),
     *                                @OA\Property(property="cost",  type="integer",  ),
     *                                @OA\Property(property="booking_id",  type="string",  ),
     *                            ),
     *               ),
     *           ),
     *       ),
     *     @OA\Response(
     *         response=201,
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
    public function create_batch(Request $request)
    {
        Validator::make($request->all(), [
            'destination_id' => 'required|exists:destinations,id',
            'accommodations' => 'array',
            'accommodations.*["accommodation_name"]' => 'required_with:accommodations|string|max:100',
            'accommodations.*["checkin_time"]' => 'date_format:Y-m-d H:i|after:today',
            'accommodations.*["checkout_time"]' => 'date_format:Y-m-d H:i|after:checkin_date',
            'accommodations.*["cost"]'=> 'numeric|min:0',
            'accommodations.*["booing_id"]'=> 'string|max:20',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_accommodations = $request['accommodations'];

        // error_log(print_r($request_accommodations, true));

        $destination->accommodations()->createMany($request_accommodations);

        return response()->json($destination, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/accommodation/search",
     *     tags={"Accommodation"},
     *     summary="Find accommodation by city",
     *     description="Find accommodation by city",
     *     operationId="search_accommodation",
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="required | string",
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
    function search(Request $request)
    {
        $faker = \Faker\Factory::create();
        $faker->seed(crc32($request->city));

        error_log(crc32($request->city));
        $num_accommodations = 10;
        $accommodations = [];

        foreach(range(1, $num_accommodations) as $i) {
            array_push($accommodations, [
                'hotel_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                'cost' => $faker->numberBetween($min = 300, $max = 2000), // $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
            ]);
        }

        return response()->json($accommodations, 200);
    }
}
