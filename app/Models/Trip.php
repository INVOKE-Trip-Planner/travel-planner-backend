<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Trip extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trip_name', 'created_by', 'start_date', 'end_date', 'cost', 'origin', 'group_type', 'trip_type', 'trip_banner',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'group_type', 'trip_type', 'pivot', 'laravel_through_key',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'destinations', 'users:id,avatar',
    ];

    /**
     * Get the destinations for the trip.
     */
    public function destinations()
    {
        return $this->hasMany('App\Models\Destination');
    }

    /**
     * Get the users for the trip.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
