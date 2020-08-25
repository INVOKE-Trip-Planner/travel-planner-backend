<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\Trip;
use App\Rules\DateNotOverlap;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Arr;

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

    /**
     * @OA\Post(
     *     path="/api/destination",
     *     tags={"Destination"},
     *     summary="Add a destination for a trip",
     *     description="Add a destination to trip after creation",
     *     operationId="create_destination",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="trip_id",
     *         in="query",
     *         description="required | exists:trips,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="required | string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:start_date",
     *         @OA\Schema(
     *             type="date"
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
            'trip_id' => 'required|exists:trips,id',
            'location' => 'required|string|max:100',
            'start_date' => 'date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'date_format:Y-m-d|after_or_equal:start_date',
            'cost'=> 'numeric|min:0',
        ])->validate();

        $trip = Trip::findOrFail($request->trip_id);

        if ($request->has('start_date') || $request->has('end_date')) {
            $destinations = $trip->destinations()->get()->toArray();
            array_push($destinations, $request->all());
            // Arr::set($destinations, 'end_date', null);
            // data_fill($destinations, '*.end_date', null);
            // data_fill($destinations, '*.start_date', null);

            Validator::make(['destinations' => $destinations], [
                'destinations' => [new DateNotOverlap('start_date', 'end_date')],
            ])->validate();
        }

        // if (Auth::id() != $trip->created_by) {
        if ($trip->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        // $request_destinations = $request['destinations'];

        // error_log(print_r($request_destinations, true));

        $destination = $trip->destinations()->create($request->except('trip_id'));

        return response()->json($destination, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/destination/update",
     *     tags={"Destination"},
     *     summary="Update destination",
     *     description="Update destination",
     *     operationId="update_destination",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:destinations,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="string | max:100",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="date_format:Y-m-d | after_or_equal:start_date",
     *         @OA\Schema(
     *             type="date"
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
            'id' => 'required|exists:destinations,id',
            'location' => 'string|max:100',
            'start_date' => 'date_format:Y-m-d|after:today',
            'end_date' => 'date_format:Y-m-d|after:start_date',
        ])->validate();

        $destination = Destination::findOrFail($request->id);

        $trip = Trip::findOrFail($destination->trip_id);

        if ($request->has('start_date') || $request->has('end_date')) {
            $destinations = $trip->destinations()->get()->toArray();
            array_push($destinations, $request->all());

            Validator::make(['destinations' => $destinations], [
                'destinations' => [new DateNotOverlap('start_date', 'end_date')],
            ])->validate();
        }

        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $destination->update($request->all());

        // return response()->json($destination, 200);
        $response = ['message' => 'The destination has been successfully updated.'];

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/destination/delete",
     *     tags={"Destination"},
     *     summary="Delete destination",
     *     description="Delete destination",
     *     operationId="delete_destination",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:destinations,id",
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
            'id' => 'required|exists:destinations,id',
        ])->validate();

        $destination = Destination::findOrFail($request->id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $destination->delete($request->id);

        $response = ['message' => 'The destination has been successfully deleted.'];

        return response()->json($response, 200);
    }

}
