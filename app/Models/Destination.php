<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Destination extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trip_id', 'start_date', 'end_date', 'cost', 'location'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * Get the trip that owns the destination.
     */
    public function trip()
    {
        return $this->belongsTo('App\Models\Trip');
    }

    /**
     * Get the transports for the destination.
     */
    public function transports()
    {
        return $this->hasMany('App\Models\Transport');
    }

    /**
     * Get the accommodations for the destination.
     */
    public function accommodations()
    {
        return $this->hasMany('App\Models\Accommodation');
    }

    /**
     * Get the itineraries for the destination.
     */
    public function itineraries()
    {
        return $this->hasMany('App\Models\Itinerary');
    }

    /**
     * Get the itineraries for the destination.
     */
    public function users()
    {
        return $this->hasManyDeep('App\Models\User',
            ['App\Models\Trip', 'trip_user'],
            // trip, trip_user, user
            [ 'id', 'trip_id', 'id'],
            // destination, trip, trip_user
            ['trip_id', 'id', 'user_id']
        );
    }

}
