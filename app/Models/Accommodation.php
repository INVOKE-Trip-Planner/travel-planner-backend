<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Accommodation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'destination_id', 'checkin_date', 'checkout_date',  'checkin_hour', 'checkout_hour',  'checkin_minute', 'checkout_minute', 'accommodation_name', 'booking_id', 'cost',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'laravel_through_key',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        // 'cost',
    ];

    protected $appends = [
        'cost',
    ];

    public function getCostAttribute()
    {
        return $this->cost()->first()['cost'];
    }
    
    /**
     * Get the cost of the accommodation.
     */
    public function cost()
    {
        return $this->morphOne('App\Models\Cost', 'costable');
    }

    /**
     * Get the destination that owns the accommodation.
     */
    public function destination()
    {
        return $this->belongsTo('App\Models\Destination');
    }

    /**
     * Get the users for the accommodation.
     */
    public function users()
    {
        return $this->hasManyDeep('App\Models\User',
            ['App\Models\Destination', 'App\Models\Trip', 'trip_user'],
            // destination, trip, trip_user, user
            [ 'id', 'id', 'trip_id', 'id'],
            // accommodation, destination, trip, trip_user
            ['destination_id' ,'trip_id', 'id', 'user_id']
        );
    }
}
