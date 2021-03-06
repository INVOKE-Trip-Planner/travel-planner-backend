<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Transport extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'destination_id', 'mode', 'departure_date', 'arrival_date', 'departure_hour', 'arrival_hour', 'departure_minute', 'arrival_minute', 'origin', 'destination', 'booking_id', 'operator', 'cost',
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
        'cost',
    ];

    public function getCostAttribute()
    {
        $cost = $this->cost()->first();

        return $cost ? (float) $cost['cost'] : null;
    }

    /**
     * Get the cost of the transport.
     */
    public function cost()
    {
        return $this->morphOne('App\Models\Cost', 'costable');
    }

    /**
     * Get the destination that owns the transport.
     */
    public function destination()
    {
        return $this->belongsTo('App\Models\Destination');
    }

    /**
     * Get the users for the transport.
     */
    public function users()
    {
        return $this->hasManyDeep('App\Models\User',
            ['App\Models\Destination', 'App\Models\Trip', 'trip_user'],
            // destination, trip, trip_user, user
            [ 'id', 'id', 'trip_id', 'id'],
            // transport, destination, trip, trip_user
            ['destination_id' ,'trip_id', 'id', 'user_id']
        );
    }
}
