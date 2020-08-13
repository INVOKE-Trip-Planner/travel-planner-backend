<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class AccommodationController extends Controller
{
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
    function find(Request $request)
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
     *     summary="Create accommodation",
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
     *                                @OA\Property(property="cost",  type="decimal",  ),
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
    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'destination_id' => 'required|exists:destinations,id',
            'accommodations' => 'array',
            'accommodations.*["accommodation_name"]' => 'required_with:destinations.*["accommodations"]|string|max:100',
            'accommodations.*["checkin_time"]' => 'date_format:Y-m-d H:i|after:today',
            'accommodations.*["checkout_time"]' => 'date_format:Y-m-d H:i|after:start_date',
            'accommodations.*["cost"]'=> 'numeric|min:0',
            'accommodations.*["booing_id"]'=> 'string|max:20',

            // 'destinations.*["itineraries"]' => 'array',
            // 'start_date' => 'date_format:Y-m-d|after:today',
            // 'end_date' => 'date_format:Y-m-d|after:start_date',
            // 'cost'=>'numeric|min:0',
            // 'group_type' => 'in:SOLO,COUPLE,FAMILY,FRIENDS',
            // 'trip_type' => 'in:WORK,LEISURE',
            // 'trip_banner' => 'image|mimes:jpeg, png, jpg, gif, svg|max:2048',
            // 'users' => 'array',
            // 'users.*' => 'required_unless:users,null|exists:users,id',
            // 'destinations' => 'array',
            // 'destinations.*["location"]' => 'required_with:destinations|string|max:100',
            // 'destinations.*["start_date"]' => 'date_format:Y-m-d|after:today',
            // 'destinations.*["end_date"]' => 'date_format:Y-m-d|after:start_date',
            // 'destinations.*["cost"]'=> 'numeric|min:0',
            // 'destinations.*["transport"]' => 'array',
            // 'destinations.*["transport"].*["mode"]' => 'in:FLIGHT,FERRY,BUS,TRAIN,OTHER|required_with:destinations.*["transport"]',
            // 'destinations.*["transport"].*["origin"]' => 'required_with:destinations.*["transport"]|string|max:100',
            // 'destinations.*["transport"].*["destination"]' => 'required_with:destinations.*["transport"]|string|max:100',
            // 'destinations.*["transport"].*["departure_time"]' => 'date_format:Y-m-d|after:today',
            // 'destinations.*["transport"].*["arrival_time"]' => 'date_format:Y-m-d|after:start_date',
            // 'destinations.*["transport"].*["cost"]'=> 'numeric|min:0',
            // 'destinations.*["transport"].*["operator"]' => 'required_with:destinations.*["transport"]|string|max:100',
            // 'destinations.*["transport"].*["booing_id"]'=> 'string|max:20',
            // 'destinations.*["accommodations"]' => 'array',
            // 'destinations.*["accommodations"].*["accommodation_name"]' => 'required_with:destinations.*["accommodations"]|string|max:100',
            // 'destinations.*["accommodations"].*["checkin_time"]' => 'date_format:Y-m-d|after:today',
            // 'destinations.*["accommodations"].*["checkout_time"]' => 'date_format:Y-m-d|after:start_date',
            // 'destinations.*["accommodations"].*["cost"]'=> 'numeric|min:0',
            // 'destinations.*["accommodations"].*["booing_id"]'=> 'string|max:20',
            // 'destinations.*["itineraries"]' => 'array',
            // 'destinations.*["itineraries"].*["date"]' => 'date_format:Y-m-d|after:today|required_with:destinations.*["itineraries"]',
            // 'destinations.*["itineraries"].*["schedule"]' => 'array|required_with:destinations.*["itineraries"]',
            // 'destinations.*["itineraries"].*["schedule"].*["activity"]' => 'string|min:1|required_with:destinations.*["itineraries"]',
            // 'destinations.*["itineraries"].*["schedule"].*["cost"]' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        $trip = $destination->trip()->first();

        if (Auth::id() != $trip->created_by) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_accommodations = $request['accommodations'];

        // error_log(print_r($request_accommodations, true));

        $destination->accommodations()->createMany($request_accommodations);

        return response()->json($destination, 201);
    }
}
