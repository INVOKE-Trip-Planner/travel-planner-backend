<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\Destination;
use App\Models\Transport;

class TransportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/transport",
     *     tags={"Transport"},
     *     summary="Get all transports for logged in user",
     *     description="Get all transports for logged in user",
     *     operationId="get_transport",
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
        $transports = Auth::user()->transports()->get();

        return response()->json($transports, 200);
    }

   /**
     * @OA\Post(
     *     path="/api/transport",
     *     tags={"Transport"},
     *     summary="Create a transport for a destination",
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
     *         name="mode",
     *         in="query",
     *         description="required | in:FLIGHT,FERRY,BUS,TRAIN,OTHER",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="required | string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="required | string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:departure_date",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_minute",
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
     *         name="operator",
     *         in="query",
     *         description="string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
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
            'mode' => 'required|in:FLIGHT,FERRY,BUS,TRAIN,OTHER',
            'origin' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'departure_date' => 'date_format:Y-m-d|after_or_equal:today',
            'arrival_date' => 'date_format:Y-m-d|after_or_equal:departure_date',
            'departure_hour' => 'numeric|min:0|max:23',
            'arrival_hour' => 'numeric|min:0|max:23',
            'departure_minute' => 'numeric|min:0|max:59',
            'arrival_minute' => 'numeric|min:0|max:59',
            'cost'=> 'numeric|min:0',
            'operator' => 'string|max:100',
            'booking_id'=> 'string|max:20',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        // $request_transports = $request['transports'];

        // error_log(print_r($request_transports, true));

        $transport = $destination->transports()->create($request->except(['destination_id', 'cost']));
        if ($request->has('cost')) {
            $transport->cost()->create($request->only('cost'));
        }

        return response()->json($transport, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/transport/update",
     *     tags={"Transport"},
     *     summary="Update transport",
     *     description="Update transport",
     *     operationId="update_transport",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:transports,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mode",
     *         in="query",
     *         description="in:FLIGHT,FERRY,BUS,TRAIN,OTHER",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="destination",
     *         in="query",
     *         description="string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:departure_date",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="departure_minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="arrival_minute",
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
     *         name="operator",
     *         in="query",
     *         description="string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
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
            'id' => 'required|exists:transports,id',
            'mode' => 'in:FLIGHT,FERRY,BUS,TRAIN,OTHER',
            'origin' => 'string|max:100',
            'destination' => 'string|max:100',
            'departure_date' => 'date_format:Y-m-d|after_or_equal:today',
            'arrival_date' => 'date_format:Y-m-d|after_or_equal:departure_date',
            'departure_hour' => 'numeric|min:0|max:23',
            'arrival_hour' => 'numeric|min:0|max:23',
            'departure_minute' => 'numeric|min:0|max:59',
            'arrival_minute' => 'numeric|min:0|max:59',
            'cost'=> 'numeric|min:0',
            'operator' => 'string|max:100',
            'booking_id'=> 'string|max:20',
        ])->validate();

        $transport = Transport::findOrFail($request->id);
        // $destination = $transport->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($transport->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $transport->update($request->except('cost'));

        if ($request->has('cost')) {
            if ($transport->cost) {
                $transport->cost()->update($request->only('cost'));
            } else {
                $transport->cost()->create($request->only('cost'));
            }
        }

        // to get updated values
        // $transport = Transport::findOrFail($request->id);
        $response = ['message' => 'The transport has been successfully updated.'];

        return response()->json($response, 200);
        // return response()->json($transport, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/transport/delete",
     *     tags={"Transport"},
     *     summary="Delete transport",
     *     description="Delete transport",
     *     operationId="delete_transport",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:transports,id",
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
            'id' => 'required|exists:transports,id',
        ])->validate();

        $transport = Transport::findOrFail($request->id);
        // $destination = $transport->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($transport->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $transport->delete($request->id);

        $response = ['message' => 'The transport has been successfully deleted.'];

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/batch/transport",
     *     tags={"Transport"},
     *     summary="Create multiple transports for a destination",
     *     description="Create transport",
     *     operationId="batch_create_transport",
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
     *               @OA\Property(property="transport",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="mode",  type="string",  ),
     *                                @OA\Property(property="departure_time",  type="string",  ),
     *                                @OA\Property(property="arrival_time",  type="string",  ),
     *                                @OA\Property(property="origin",  type="string",  ),
     *                                @OA\Property(property="destination",  type="string",  ),
     *                                @OA\Property(property="cost",  type="integer",  ),
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
    public function create_batch(Request $request)
    {
        Validator::make($request->all(), [
            'destination_id' => 'required|exists:destinations,id',
            'transports' => 'array',
            'transports.*["mode"]' => 'in:FLIGHT,FERRY,BUS,TRAIN,OTHER|required_with:transports',
            'transports.*["origin"]' => 'required_with:transports|string|max:100',
            'transports.*["destination"]' => 'required_with:transports|string|max:100',
            'transports.*["departure_time"]' => 'date_format:Y-m-d H:i|after_or_equal:today',
            'transports.*["arrival_time"]' => 'date_format:Y-m-d H:i|after_or_equal:departure_time',
            'transports.*["cost"]'=> 'numeric|min:0',
            'transports.*["operator"]' => 'string|max:100',
            'transports.*["booing_id"]'=> 'string|max:20',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_transports = $request['transports'];

        // error_log(print_r($request_transports, true));

        $destination->transports()->createMany($request_transports);

        return response()->json($destination, 201);
    }
}
