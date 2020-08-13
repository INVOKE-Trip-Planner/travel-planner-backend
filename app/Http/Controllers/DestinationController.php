<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class DestinationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/destination",
     *     tags={"Destination"},
     *     summary="Get all destinations for logged in user",
     *     description="Get all destinations for logged in user",
     *     operationId="get_destination",
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
        $destinations = Auth::user()->destinations()->get();

        return response()->json($destinations, 200);
    }
}
