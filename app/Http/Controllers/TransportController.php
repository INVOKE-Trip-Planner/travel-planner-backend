<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\Destination;

class TransportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/transport",
     *     tags={"Transport"},
     *     summary="Create transport",
     *     description="Create transport",
     *     operationId="create_transport",
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
     *               @OA\Property(property="transport",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="mode",  type="string",  ),
     *                                @OA\Property(property="departure_time",  type="string",  ),
     *                                @OA\Property(property="arrival_time",  type="string",  ),
     *                                @OA\Property(property="origin",  type="string",  ),
     *                                @OA\Property(property="destination",  type="string",  ),
     *                                @OA\Property(property="cost",  type="decimal",  ),
     *                                @OA\Property(property="operator",  type="string",  ),
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
            'transports' => 'array',
            'transports.*["mode"]' => 'in:FLIGHT,FERRY,BUS,TRAIN,OTHER|required_with:transports',
            'transports.*["origin"]' => 'required_with:transports|string|max:100',
            'transports.*["destination"]' => 'required_with:transports|string|max:100',
            'transports.*["departure_time"]' => 'date_format:Y-m-d H:i|after:today',
            'transports.*["arrival_time"]' => 'date_format:Y-m-d H:i|after:departure_time',
            'transports.*["cost"]'=> 'numeric|min:0',
            'transports.*["operator"]' => 'required_with:transports|string|max:100',
            'transports.*["booing_id"]'=> 'string|max:20',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        $trip = $destination->trip()->first();

        if (Auth::id() != $trip->created_by) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_transports = $request['transports'];

        // error_log(print_r($request_transports, true));

        $destination->transports()->createMany($request_transports);

        return response()->json($destination, 201);
    }
}
