<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    private function flatten_cost($trips) {
        // TODO: optimize the algorithm
        foreach($trips as $trip) {
            $trip_cost = [];
            foreach($trip->destinations as $destination) {
                $destination_cost = [];
                // $transport_cost = [];
                // $accommodation_cost = [];
                // $itinerary_cost = [];

                foreach($destination->transports as $transport) {
                    // error_log( $transport->cost['cost']);
                    // $transport->total = $transport->cost['cost'];
                    // $transport->cost = $transport->cost()->first()['cost'];
                    // array_push($transport_cost, $transport->cost);
                    array_push($destination_cost, $transport->cost);
                    array_push($trip_cost, $transport->cost);
                }

                foreach($destination->accommodations as $accommodation) {
                    // $accommodation->cost = $accommodation->cost()->first()['cost'];
                    // array_push($transport_cost, $accommodation->cost);
                    array_push($destination_cost, $accommodation->cost);
                    array_push($trip_cost, $accommodation->cost);
                }

                foreach($destination->itineraries as $itinerary) {
                    foreach($itinerary->schedules as $schedule) {
                        // error_log($schedule->cost());
                        // $schedule->cost = $schedule->cost()->first()['cost'];
                        // array_push($itinerary_cost, $schedule->cost);
                        array_push($destination_cost, $schedule->cost);
                        array_push($trip_cost, $schedule->cost);
                    }
                }
                // $destination->transports->cost = sprintf("%01.2f", array_sum($transport_cost));
                // $destination->accommodations->cost = sprintf("%01.2f", array_sum($accommodation_cost));
                // $destination->itineraries->cost = sprintf("%01.2f", array_sum($itinerary_cost));

                $destination->cost = sprintf("%01.2f", array_sum($destination_cost));
                // $trip_cost = array_merge($trip_cost, $destination_cost);
            }
            $trip->cost = sprintf("%01.2f", array_sum($trip_cost));
        }
        // array_walk_recursive(
        //     $trips,
        //     function(&$value, $key) {
        //         if ($key === 'cost') {
        //             $value['cost'] = $value['cost']['cost'];
        //             // Arr::flatten($value);
        //         }
        //     }
        // );

        // error_log(print_r($trips->pluck('destinations.*.transports.*.cost')->all(), true));

        return $trips;
    }

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
     *         name="start_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:start_date",
     *         @OA\Schema(
     *             type="date"
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
        Validator::make($request->all(), [
            'trip_name' => 'string|max:255',
            'origin' => 'string|max:100',
            'start_date' => 'date_format:Y-m-d|after:today',
            'end_date' => 'date_format:Y-m-d|after:start_date',
            'group_type' => 'in:SOLO,COUPLE,FAMILY,FRIENDS',
            'trip_type' => 'in:WORK,LEISURE',
            'trip_banner' => 'image|mimes:jpeg, png, jpg, gif, svg|max:2048',
            'users' => 'array',
            'users.*' => 'required_unless:users,null|exists:users,id',
            'destinations' => 'array',
            'destinations.*["location"]' => 'required_with:destinations|string|max:100',
            'destinations.*["start_date"]' => 'date_format:Y-m-d|after:today',
            'destinations.*["end_date"]' => 'date_format:Y-m-d|after:start_date',
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

        $data = $request->except(['trip_banner', 'created_by', 'users', 'destinations']);
        $data['created_by'] = Auth::id();

        $trip = Trip::create($data);

        $users = [Auth::id()];

        if ($request->has('users')) {
            // error_log(print_r($request->users, true));
            // error_log(print_r($users, true));
            $users = array_merge($users, $request->users);
        }
        // error_log(print_r($users, true));

        // attach or sync to manage many to many relationships
        // sync will delete if not exists
        $trip->users()->sync($users);

        if ($request->has('trip_banner')) {
            $bannerName = $trip->id . '_banner' . time() . '.' .request()->trip_banner->getClientOriginalExtension();
            $request->trip_banner->storeAs('trip_banners', $bannerName);
            $trip->trip_banner = $bannerName;
            $trip->save();
        }

        if ($request->has('destinations')) {
            $request_destinations = $request->destinations;
            // $request_destinations = $request->only('destinations')['destinations'];
            // error_log(print_r($request_destinations[0], true));
            foreach ($request_destinations as &$destination) {
                if (is_string($destination)) {
                    $destination = (array) json_decode($destination);
                }
            }
            // error_log(print_r($request_destinations, true));
            // $request_destinations = array_map(function($arr) use ($trip){
            //     return $arr + ['trip_id' => $trip->id];
            // }, $request_destinations);
            // data_fill($request_destinations, '*.trip_id', $trip->id);

            // Arr::except($request_destinations, '*.transports');
            // error_log(print_r($request_destinations, true));

            // Destination::insert(($request_destinations));
            $trip->destinations()->createMany($request_destinations);
        }

        // To get trip's users & destinations
        $trip = Trip::find($trip->id);
        // $trip->users = $trip->users()->select('id', 'avatar')->get();
        // $trip->destinations = $trip->destinations()->get();
        // $trip = $this->get_destinations_details($trip);

        // error_log(print_r($trip, true));

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
     *         description="required | string | max:255",
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
     *         name="start_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:today",
     *         @OA\Schema(
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="date_format:Y-m-d | after:start_date",
     *         @OA\Schema(
     *             type="date"
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
            'id' => 'required|exists:trips',
            'trip_name' => 'required|string|max:255',
            'origin' => 'string|max:100',
            'start_date' => 'date_format:Y-m-d|after:today',
            'end_date' => 'date_format:Y-m-d|after:start_date',
            'group_type' => 'in:SOLO,COUPLE,FAMILY,FRIENDS',
            'trip_type' => 'in:WORK,LEISURE',
            'trip_banner' => ['image', 'mimes:jpeg, png, jpg, gif, svg', 'max:2048'],
            'users' => 'array',
            'users.*' => 'required_unless:users,null|exists:users,id',

            // 'destinations' => 'array',
            // 'destinations.*["location"]' => 'required_with:destinations|string|max:100',
            // 'destinations.*["start_date"]' => 'date_format:Y-m-d|after:today',
            // 'destinations.*["end_date"]' => 'date_format:Y-m-d|after:start_date',
            // 'destinations.*["cost"]'=> 'numeric|min:0',
            // 'destinations.*["transport"]' => 'array',
        ])->validate();

        $trip = Trip::findOrFail(request('id'));

        // if (Auth::id() != $trip->created_by) {
        if ($trip->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $data = $request->except(['trip_banner', 'created_by', 'users']);

        if ($request->has('trip_banner')) {
            $bannerName = $trip->id . '_banner' . time() . '.' .request()->trip_banner->getClientOriginalExtension();
            $request->trip_banner->storeAs('trip_banners', $bannerName);
            $trip->trip_banner = $bannerName;
            $trip->save();
        }

        if ($request->has('users')) {
            $users = [Auth::id()];
            array_merge($users, $request->users);
            $trip->users()->sync($users);
        }

        // if ($request->has('destinations')) {
        //     $request_destinations = $request->only('destinations')['destinations'];
        //     $trip->destinations()->delete();
        //     $trip->destinations()->createMany($request_destinations);
        // }

        $trip->update($data);

        // return response()->json($trip, 200);
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
        $start_time = microtime(true);

        // Eager Loading ??
        // $trips = Trip::with(['user', 'destination', 'transport', 'accommodation', 'itinerary'])->get();
        // $users = User::select('id', 'avatar')->with('trips')->get();

        $trips = Auth::user()
                    ->trips()
                    ->orderByRaw(DB::raw("-start_date desc"))
                    // ->orderBy('start_date')
                    ->get();
                    // ->toArray();

        // foreach ($trips as $trip) {
        //     $trip->users = $trip->users()->select('id', 'avatar')->get();
        //     $trip->destinations = $trip->destinations()->get();
        //     $trip = $this->get_destinations_details($trip);
        // }

        // $trips->each->destinations->transform(function($destination, $key) {
        //     $destination->each->transports->transform(function($transport, $key) {
        //         $transport->cost = $transport->cost['cost'];
        //         return $transport;
        //     });
        //     return $destination;
        // });

        // $this->flatten_cost($trips);

        $execution_time = microtime(true) - $start_time;
        error_log("Execution time of register = $execution_time");

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
     *         response=204,
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

        // Only creator can delete trip
        // if (Auth::id() != $trip->created_by) {
        if ($trip->users()->find(Auth::id()) === null) {
            $response = ['message' => 'Unauthorized'];
            return response($response, 401);
        }

        $trip->delete();

        $response = ['message' => 'Trip is successfully deleted.'];
        return response()->json($response, 204);
    }
}
