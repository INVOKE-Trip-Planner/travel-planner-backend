<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Schedule extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'itinerary_id', 'minute', 'hour', 'title', 'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'laravel_through_key',
    ];

    protected $appends = [
        'cost'
    ];

    public function getCostAttribute()
    {
        $cost = $this->cost()->first();

        return $cost ? (float) $cost['cost'] : null;
    }

    /**
     * Get the cost of the schedule.
     */
    public function cost()
    {
        return $this->morphOne('App\Models\Cost', 'costable');
    }

    /**
     * Get the itinerary that owns the schedule.
     */
    public function itinerary()
    {
        return $this->belongsTo('App\Models\Itinerary');
    }

    /**
     * Get the destinations for the schedule.
     */
    public function destinations()
    {
        return $this->hasManyDeep('App\Models\Destination',
            ['App\Models\Itinerary'],
            // itinerary, destination,
            [ 'id', 'id'],
            // schedule, itinerary
            [ 'itinerary_id', 'destination_id']
        );
    }

    /**
     * Get the users for the schedule.
     */
    public function users()
    {
        return $this->hasManyDeep('App\Models\User',
            ['App\Models\Itinerary', 'App\Models\Destination', 'App\Models\Trip', 'trip_user'],
            // itinerary, destination, trip, trip_user, user
            [ 'id', 'id', 'id', 'trip_id', 'id'],
            // schedule, itinerary, destination, trip, trip_user
            [ 'itinerary_id', 'destination_id' ,'trip_id', 'id', 'user_id']
        );
    }
}
