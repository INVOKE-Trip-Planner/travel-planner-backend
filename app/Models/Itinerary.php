<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Itinerary extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'destination_id', 'day', // 'date', 'schedule', 'cost',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'laravel_through_key',
    ];

    // protected $casts = [
    //     'schedule' => 'array',
    // ];

    /**
     * Get the destination that owns the itinerary.
     */
    public function destination()
    {
        return $this->belongsTo('App\Models\Destination');
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'schedules',
    ];

    /**
     * Get the schedules for the itinerary.
     */
    public function schedules()
    {
        return $this->hasMany('App\Models\Schedule');
    }

    /**
     * Get the users for the itinerary.
     */
    public function users()
    {
        return $this->hasManyDeep('App\Models\User',
            ['App\Models\Destination', 'App\Models\Trip', 'trip_user'],
            // destination, trip, trip_user, user
            [ 'id', 'id', 'trip_id', 'id'],
            // itinerary, destination, trip, trip_user
            ['destination_id' ,'trip_id', 'id', 'user_id']
        );
    }

    // public function update_schedules($new_schedules) {
    //     $costs = [];

    //     foreach ($this->schedules() as $schedule) {
    //         array_push($schedule->cost()->id);
    //     }

    //     Cost::destroy($costs);
    // }
}
