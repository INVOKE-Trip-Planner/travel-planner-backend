<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\Itinerary;

class ItineraryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/itinerary",
     *     tags={"Itinerary"},
     *     summary="Get all itineraries for logged in user",
     *     description="Get all itineraries for logged in user",
     *     operationId="get_itinerary",
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
        $itineraries = Auth::user()->itineraries()->get();

        return response()->json($itineraries, 200);
    }

   /**
     * @OA\Post(
     *     path="/api/itinerary",
     *     tags={"Itinerary"},
     *     summary="Create a itinerary for a destination",
     *     description="Create itinerary",
     *     operationId="create_itinerary",
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
     *         name="date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *           name="body",
     *           in="query",
     *           required=false,
     *           explode=true,
     *           @OA\Schema(
     *               @OA\Property(property="schedule",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="activity",  type="string",  ),
     *                                @OA\Property(property="cost",  type="integer",  ),
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
            'date' => 'date_format:Y-m-d|after:today', // |required',
            'schedule' => 'required|array',
            'schedule.*["activity"]' => 'required|string|min:1',
            'schedule.*["cost"]' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        // $request_itineraries = $request['itineraries'];
        // error_log(print_r($request_itineraries, true));

        $itinerary = $destination->itineraries()->create($request->except('destination_id'));

        return response()->json($itinerary, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/itinerary/update",
     *     tags={"Itinerary"},
     *     summary="Update itinerary",
     *     description="Update itinerary",
     *     operationId="update_itinerary",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:itineraries,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *           name="body",
     *           in="query",
     *           required=false,
     *           explode=true,
     *           @OA\Schema(
     *               @OA\Property(property="schedule",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="activity",  type="string",  ),
     *                                @OA\Property(property="cost",  type="integer",  ),
     *                            ),
     *               ),
     *           ),
     *       ),
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
            'id' => 'required|exists:itineraries,id',
            'date' => 'date_format:Y-m-d|after:today',
            'schedule' => 'array',
            'schedule.*["activity"]' => 'required_with:schedule|string|min:1',
            'schedule.*["cost"]' => 'numeric|min:0',
        ])->validate();

        $itinerary = Itinerary::findOrFail($request->id);
        // $destination = $itinerary->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($itinerary->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $itinerary->update($request->all());

        return response()->json($itinerary, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/itinerary/delete",
     *     tags={"Itinerary"},
     *     summary="Delete itinerary",
     *     description="Delete itinerary",
     *     operationId="delete_itinerary",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:itineraries,id",
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
            'id' => 'required|exists:itineraries,id',
        ])->validate();

        $itinerary = Itinerary::findOrFail($request->id);
        // $destination = $itinerary->destination()->first();
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($itinerary->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $itinerary->delete($request->id);

        $response = ['message' => 'The itinerary has been successfully deleted.'];

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/batch/itinerary",
     *     tags={"Itinerary"},
     *     summary="Create multiple itineraries for a destination",
     *     description="Create multiple itineraries for a destination",
     *     operationId="batch_create_itinerary",
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
     *               @OA\Property(property="itineraries",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *               @OA\Property(property="date",  type="string",  ),
     *               @OA\Property(property="schedule",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="activity",  type="string",  ),
     *                                @OA\Property(property="cost",  type="integer",  ),
     *                            ),
     *               ),
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
            'itineraries' => 'array',
            'itineraries.*["date"]' => 'date_format:Y-m-d|after:today', // |required_with:itineraries',
            'itineraries.*["schedule"]' => 'array|required_with:itineraries',
            'itineraries.*["schedule"].*["activity"]' => 'string|min:1|required_with:itineraries',
            'itineraries.*["schedule"].*["cost"]' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);
        // $trip = $destination->trip()->first();
        // if (Auth::id() != $trip->created_by) {
        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_itineraries = $request['itineraries'];

        // error_log(print_r($request_itineraries, true));

        $destination->itineraries()->createMany($request_itineraries);

        return response()->json($destination, 201);
    }
}
