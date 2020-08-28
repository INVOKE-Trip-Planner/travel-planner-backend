<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Validator;
use Auth;

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/schedule",
     *     tags={"Schedule"},
     *     summary="Get all schedules for logged in user",
     *     description="Get all schedules for logged in user",
     *     operationId="get_schedule",
     *     security={{"bearerAuth":{}}},
     *     deprecated=true,
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
        $schedules = Auth::user()->schedules()->get();

        return response()->json($schedules, 200);
    }

   /**
     * @OA\Post(
     *     path="/api/schedule",
     *     tags={"Schedule"},
     *     summary="Add a schedule for an itinerary",
     *     description="Add schedule",
     *     operationId="create_schedule",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="itinerary_id",
     *         in="query",
     *         description="required | exists:itineraries,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="required | string | min:1",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="string",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cost",
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
    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'itinerary_id' => 'required|exists:itineraries,id',
            'hour' => 'numeric|min:0|max:23',
            'minute' => 'numeric|min:0|max:59',
            'title' => 'required|string|min:1',
            'description' => 'string',
            'cost' => 'numeric|min:0',
        ])->validate();

        $itinerary = Itinerary::find($request->itinerary_id);

        if ($itinerary->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $schedule = $itinerary->schedules()->create($request->except(['cost']));

        if ($request->has('cost')) {
            $schedule->cost()->create($request->only('cost'));
        }

        return response()->json($schedule, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/schedule/update",
     *     tags={"Schedule"},
     *     summary="Update schedule",
     *     description="Update schedule",
     *     operationId="update_schedule",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:schedules,id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="hour",
     *         in="query",
     *         description="numeric | min:0 | max:23",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minute",
     *         in="query",
     *         description="numeric | min:0 | max:59",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="string | min:1",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="string",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="cost",
     *         in="query",
     *         description="numeric | min:0",
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
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'id' => 'required|exists:schedules,id',
            'hour' => 'numeric|min:0|max:23',
            'minute' => 'numeric|min:0|max:59',
            'title' => 'string|min:1',
            'description' => 'string',
            'cost' => 'numeric|min:0',
        ])->validate();

        $schedule = Schedule::findOrFail($request->id);

        if ($schedule->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $schedule->update($request->except(['cost']));

        if ($request->has('cost')) {
            if ($schedule->cost) {
                $schedule->cost()->update($request->only('cost'));
            } else {
                $schedule->cost()->create($request->only('cost'));
            }
        }

        $response = ['message' => 'The schedule has been successfully updated.'];

        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/schedule/delete",
     *     tags={"Schedule"},
     *     summary="Delete schedule",
     *     description="Delete schedule",
     *     operationId="delete_schedule",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:schedules,id",
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
            'id' => 'required|exists:schedules,id',
        ])->validate();

        $schedule = Schedule::findOrFail($request->id);

        if ($schedule->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $schedule->delete($request->id);

        $response = ['message' => 'The schedule has been successfully deleted.'];

        return response()->json($response, 200);
    }

}
