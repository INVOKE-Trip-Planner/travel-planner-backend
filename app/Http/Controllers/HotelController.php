<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/hotel",
     *     tags={"Hotel"},
     *     summary="Find hotels by city",
     *     description="Find hotels by city",
     *     operationId="get_hotels",
     *     security={{"bearerAuth":{}}},
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
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error"
     *     )
     * )
     */
    function find(Request $request)
    {
        $start_time = microtime(true);

        $faker = \Faker\Factory::create();
        $faker->seed(crc32($request->city));

        error_log(crc32($request->city));
        $num_hotels = 10;
        $hotels = [];

        foreach(range(1, $num_hotels) as $i) {
            array_push($hotels, [
                'hotel_name' => $faker->company . ' ' . $faker->randomElement($array = ['Hotel', 'Resort', 'Suite', 'Homestay', 'Hostel']),
                'cost' => $faker->randomFloat($nbMaxDecimals = 2, $min = 30, $max = 2000),
            ]);
        }

        return response()->json($hotels, 200);
    }
}
