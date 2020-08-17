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
        'itinerary_id', 'minute', 'hour', 'title', 'description', // 'cost',
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
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        // 'cost',
    ];

    // this is a recommended way to declare event handlers
    // public static function boot() {
    //     parent::boot();

    //     static::deleting(function($schedule) { // before delete() method call this
    //          $schedule->cost()->delete();
    //          // do the rest of the cleanup...
    //     });
    // }

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
