<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Rules\DateNotOverlap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/trip",
     *     tags={"Trip"},
     *     summary="Create trip",
     *     description="Create trip",
     *     operationId="create_trip",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="trip_banner",
     *                      type="file",
     *                      description="image | mimes:jpeg, png, jpg, gif, svg | max:2048",
     *                      @OA\Items(type="string", format="binary")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="trip_name",
     *         in="query",
     *         description="string | max:255",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="string | max:100",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="group_type",
     *         in="query",
     *         description="in:SOLO,COUPLE,FAMILY,FRIENDS",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="trip_type",
     *         in="query",
     *         description="in:WORK,LEISURE",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *           name="body",
     *           in="query",
     *           required=false,
     *           explode=true,
     *           @OA\Schema(
     *               @OA\Property(property="users",
     *                            type="array",
     *                            @OA\Items(
     *                                type="integer",
     *                            ),
     *               ),
     *               @OA\Property(property="destinations",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="location",  type="string",  ),
     *                                @OA\Property(property="start_date",  type="string",  ),
     *                                @OA\Property(property="end_date",  type="string",  ),
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
        if ($request->has('destinations')) {
            $request_destinations = $request->destinations;
            foreach ($request_destinations as &$destination) {
                if (is_string($destination)) {
                    $destination = (array) json_decode($destination);
                }
            }

            Validator::make(['destinations' => $request_destinations], [
                'destinations' => ['array', new DateNotOverlap('start_date', 'end_date')],
                'destinations.*["location"]' => 'required_with:destinations|string|max:100',
                'destinations.*["start_date"]' => 'date_format:Y-m-d|after_or_equal:today',
                'destinations.*["end_date"]' => 'date_format:Y-m-d|after_or_equal:start_date',
            ])->validate();
        }

        Validator::make($request->all(), [
            'trip_name' => 'string|max:255',
            'origin' => 'string|max:100',
            'group_type' => 'in:SOLO,COUPLE,FAMILY,FRIENDS',
            'trip_type' => 'in:WORK,LEISURE',
            'trip_banner' => 'image|mimes:jpeg, png, jpg, gif, svg|max:2048',
            'users' => 'array',
            'users.*' => 'required_unless:users,null|exists:users,id',
        ])->validate();

        $data = $request->except(['trip_banner', 'created_by', 'users', 'destinations']);
        $data['created_by'] = Auth::id();

        $trip = Trip::create($data);

        $users = [Auth::id()];

        if ($request->has('users')) {
            $users = array_merge($users, $request->users);
        }

        $trip->users()->sync($users);

        if ($request->has('trip_banner')) {
            $bannerName = $trip->id . '_banner' . time() . '.' .request()->trip_banner->getClientOriginalExtension();
            $request->trip_banner->storeAs('trip_banners', $bannerName, 'public');
            $trip->trip_banner = $bannerName;
            $trip->save();
        }

        if ($request->has('destinations')) {
            $trip->destinations()->createMany($request_destinations);
        }

        $trip = Trip::find($trip->id);

        return response()->json($trip, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/trip/update",
     *     tags={"Trip"},
     *     summary="Update trip",
     *     description="Update trip",
     *     operationId="update_trip",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="trip_banner",
     *                      type="file",
     *                      description="image | mimes:jpeg, png, jpg, gif, svg | max:2048",
     *                      @OA\Items(type="string", format="binary")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:trips",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="trip_name",
     *         in="query",
     *         description="string | max:255",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin",
     *         in="query",
     *         description="string | max:100",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="group_type",
     *         in="query",
     *         description="in:SOLO,COUPLE,FAMILY,FRIENDS",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="trip_type",
     *         in="query",
     *         description="in:WORK,LEISURE",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *           name="body",
     *           in="query",
     *           required=false,
     *           explode=true,
     *           @OA\Schema(
     *               @OA\Property(property="users",
     *                            type="array",
     *                            @OA\Items(
     *                                type="integer",
     *                            ),
     *               ),
     *               @OA\Property(property="destinations",
     *                            type="array",
     *                            @OA\Items(
     *                                type="object",
     *                                @OA\Property(property="location",  type="string",  ),
     *                                @OA\Property(property="start_date",  type="string",  ),
     *                                @OA\Property(property="end_date",  type="string",  ),
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
        if ($request->has('destinations')) {
            $request_destinations = $request->destinations;
            error_log(print_r($request_destinations, true));
            $destination_ids = [];
            $destination_update = [];
            $destination_create = [];

            foreach ($request_destinations as &$destination) {
                if (is_string($destination)) {
                    $destination = (array) json_decode($destination);
                }
                if (isset($destination['id'])) {
                    array_push($destination_ids, $destination['id']);
                    array_push($destination_update, $destination);
                } else {
                    array_push($destination_create, $destination);
                }
            }

            Validator::make(['destinations' => $request_destinations], [
                'destinations' => ['array', new DateNotOverlap('start_date', 'end_date')],
                'destinations.*["id"]' => 'numeric|exists:destinations,id',
                'destinations.*["location"]' => 'required_with:destinations|string|max:100',
                'destinations.*["start_date"]' => 'date_format:Y-m-d|after_or_equal:today',
                'destinations.*["end_date"]' => 'date_format:Y-m-d|after_or_equal:start_date',
            ])->validate();
        }

        Validator::make($request->all(), [
            'id' => 'required|exists:trips',
            'trip_name' => 'string|max:255',
            'origin' => 'string|max:100',
            'group_type' => 'in:SOLO,COUPLE,FAMILY,FRIENDS',
            'trip_type' => 'in:WORK,LEISURE',
            'trip_banner' => ['image', 'mimes:jpeg, png, jpg, gif, svg', 'max:2048'],
            'users' => 'array',
            'users.*' => 'required_unless:users,null|exists:users,id',
        ])->validate();

        $trip = Trip::findOrFail(request('id'));

        if ($trip->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $data = $request->except(['trip_banner', 'created_by', 'users']);

        if ($request->has('trip_banner')) {
            $bannerName = $trip->id . '_banner' . time() . '.' .request()->trip_banner->getClientOriginalExtension();
            $request->trip_banner->storeAs('trip_banners', $bannerName, 'public');
            $trip->trip_banner = $bannerName;
            $trip->save();
        }

        if ($request->has('users')) {
            $users = [Auth::id()];
            $users = array_merge($users, $request->users);
            $trip->users()->sync($users);
        }

        if ($request->has('destinations')) {
            $request_destinations = $request->only('destinations')['destinations'];

            $trip->destinations()->whereNotIn('destinations.id', $destination_ids)->delete();

            foreach($destination_update as $destination) {
                $trip->destinations()->findOrFail($destination['id'])->fill($destination)->save();
            }

            $trip->destinations()->createMany($destination_create);
        }

        $trip->update($data);

        $response = ['message' => 'The trip has been successfully updated.'];

        return response()->json($response, 200);
    }


    /**
     * @OA\Get(
     *     path="/api/trip",
     *     tags={"Trip"},
     *     summary="Get all trips for logged in user",
     *     description="Get all trips excluding deleted ones",
     *     operationId="get_trip",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
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
        $trips = Auth::user()
                    ->trips()
                    ->leftJoin(DB::raw('(select min(start_date) as start_date, trips.id from trips left join destinations on trips.id = destinations.trip_id group by trips.id) trip_agg'),
                    function($join) {
                        $join->on('trips.id', '=', 'trip_agg.id');
                    })
                    ->orderByRaw(DB::raw("-start_date desc"))
                    ->get();

        return response()->json($trips, 200);
    }

    //
    /**
     * @OA\Post(
     *     path="/api/trip/delete",
     *     tags={"Trip"},
     *     summary="Delete trip",
     *     description="Delete trip",
     *     operationId="delete_trip",
     *     security={{"bearerAuth":{}}},
     *     deprecated=false,
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="required | exists:trips,id",
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
            'id' => 'required|exists:trips,id',
        ])->validate();

        $trip = Trip::findOrFail(request('id'));

        if ($trip->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $trip->delete();

        $response = ['message' => 'Trip is successfully deleted.'];
        return response()->json($response, 200);
    }

}
