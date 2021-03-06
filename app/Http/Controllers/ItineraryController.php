<?php

namespace App\Http\Controllers;

use App\Models\Cost;
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
     *     summary="Create an itinerary for a destination",
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
     *         name="day",
     *         in="query",
     *         description="numeric | min:0",
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
     *               @OA\Property(property="schedules",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="hour",  type="integer",  ),
     *                                @OA\Property(property="minute",  type="integer",  ),
     *                                @OA\Property(property="title",  type="string",  ),
     *                                @OA\Property(property="description",  type="string",  ),
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
            'day' => 'numeric|min:0', // |required',
            'schedules' => 'required|array',
            'schedules.*["hour"]' => 'numeric|min:0|max:23',
            'schedules.*["minute]' => 'numeric|min:0|max:59',
            'schedules.*["title"]' => 'required|string|min:1',
            'schedules.*["description"]' => 'string|min:1',
            'schedules.*["cost"]' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);

        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $itinerary = $destination->itineraries()->create($request->except(['destination_id', 'schedules']));

        $request_schedules = $request->schedules;
        foreach ($request_schedules as &$schedule) {
            if (is_string($schedule)) {
                $schedule = (array) json_decode($schedule);
            }
        }

        $schedules = $itinerary->schedules()->createMany($request_schedules);
        $schedule_ids = array_column($schedules->toArray(), 'id');
        $costs = array_column($request_schedules, 'cost');

        $new_costs = array_map(function($schedule_id, $cost) {
            return [
                'costable_id' => $schedule_id,
                'cost' => $cost,
                'costable_type' => 'App\Models\Schedule',
            ];
        }, $schedule_ids, $costs);

        Cost::insert($new_costs);

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
     *         name="day",
     *         in="query",
     *         description="numeric | min:0",
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
     *               @OA\Property(property="schedules",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="hour",  type="integer",  ),
     *                                @OA\Property(property="minute",  type="integer",  ),
     *                                @OA\Property(property="title",  type="string",  ),
     *                                @OA\Property(property="description",  type="string",  ),
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
            'day' => 'numeric|min:0',
            'schedules' => 'array',
            'schedules.*["hour"]' => 'numeric|min:0|max:23',
            'schedules.*["minute]' => 'numeric|min:0|max:59',
            'schedules.*["title"]' => 'required_with:schedules|string|min:1',
            'schedules.*["description"]' => 'string|min:1',
            'schedules.*["cost"]' => 'numeric|min:0',
        ])->validate();

        $itinerary = Itinerary::findOrFail($request->id);

        if ($itinerary->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $itinerary->update($request->except(['cost', 'schedules']));

        if ($request->has('schedules')) {
            $old_costs = [];

            foreach ($itinerary->schedules()->get() as $schedule) {
                array_push($old_costs, $schedule->cost()->first()->id);
            }

            Cost::destroy($old_costs);

            $itinerary->schedules()->delete();

            $request_schedules = $request->schedules;
            foreach ($request_schedules as &$schedule) {
                if (is_string($schedule)) {
                    $schedule = (array) json_decode($schedule);
                }
            }

            $schedules = $itinerary->schedules()->createMany($request_schedules);
            $schedule_ids = array_column($schedules->toArray(), 'id');
            $costs = array_column($request_schedules, 'cost');

            $new_costs = array_map(function($schedule_id, $cost) {
                return [
                    'costable_id' => $schedule_id,
                    'cost' => $cost,
                    'costable_type' => 'App\Models\Schedule',
                ];
            }, $schedule_ids, $costs);
            Cost::insert($new_costs);
        }

        $response = ['message' => 'The itinerary has been successfully updated.'];

        return response()->json($response, 200);
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
     *     path="/api/itinerary/create",
     *     tags={"Itinerary"},
     *     summary="Create an itinerary for a destination without schedules",
     *     description="Create only itinerary",
     *     operationId="create_only_itinerary",
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
     *         name="day",
     *         in="query",
     *         description="numeric | min:0",
     *         @OA\Schema(
     *             type="integer"
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
    public function create_only_itinerary(Request $request)
    {
        Validator::make($request->all(), [
            'destination_id' => 'required|exists:destinations,id',
            'day' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);

        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $itinerary = $destination->itineraries()->create($request->all());

        return response()->json($itinerary, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/batch/itinerary",
     *     tags={"Itinerary"},
     *     summary="Create multiple itineraries for a destination",
     *     description="Create multiple itineraries for a destination",
     *     operationId="batch_create_itinerary",
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
            'itineraries.*["date"]' => 'date_format:Y-m-d|after:today',
            'itineraries.*["schedule"]' => 'array|required_with:itineraries',
            'itineraries.*["schedule"].*["activity"]' => 'string|min:1|required_with:itineraries',
            'itineraries.*["schedule"].*["cost"]' => 'numeric|min:0',
        ])->validate();

        $destination = Destination::find($request->destination_id);

        if ($destination->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $request_itineraries = $request['itineraries'];

        $destination->itineraries()->createMany($request_itineraries);

        return response()->json($destination, 201);
    }
}
