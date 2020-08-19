<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        'created_at', 'updated_at', 'laravel_through_key', 'transportCosts', 'accommodationCosts', 'itineraryCosts'
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'transports', 'accommodations', 'itineraries', 'transportCosts', 'accommodationCosts', 'itineraryCosts'
    ];

    protected $appends = [
        'subtotal'
    ];

    public function getSubtotalAttribute()
    {
        return round($this->transportCosts()->get()->sum('cost')
            + $this->accommodationCosts()->get()->sum('cost')
            + $this->itineraryCosts()->get()->sum('cost'), 2);
    }

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
        return $this->hasMany('App\Models\Transport')->orderByRaw(DB::raw("-departure_date desc"));;
    }

    /**
     * Get the accommodations for the destination.
     */
    public function accommodations()
    {
        return $this->hasMany('App\Models\Accommodation')->orderByRaw(DB::raw("-checkin_date desc"));;
    }

    /**
     * Get the itineraries for the destination.
     */
    public function itineraries()
    {
        return $this->hasMany('App\Models\Itinerary')->orderByRaw(DB::raw("-day desc"));
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

    /**
     * Get the users for the transport cost.
     */
    public function transportCosts()
    {
        return $this->hasManyDeep('App\Models\Cost',
            ['App\Models\Transport'],
            // transport, cost
            [ null, ['costable_type', 'costable_id']],
        );
    }

    /**
     * Get the users for the accommodation cost.
     */
    public function accommodationCosts()
    {
        return $this->hasManyDeep('App\Models\Cost',
            ['App\Models\Accommodation'],
            // accommodation, cost
            [ null, ['costable_type', 'costable_id']],
        );
    }

    /**
     * Get the users for the itinerary cost.
     */
    public function itineraryCosts()
    {
        return $this->hasManyDeep('App\Models\Cost',
            ['App\Models\Itinerary', 'App\Models\Schedule'],
            // itinerary, schedule, cost
            [ null, null, ['costable_type', 'costable_id']],
        );
    }

}
