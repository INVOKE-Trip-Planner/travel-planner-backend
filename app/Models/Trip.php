<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        'trip_name', 'created_by', 'cost', 'origin', 'group_type', 'trip_type', 'trip_banner', // 'start_date', 'end_date',
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
        'destinations', 'users:id,name,avatar',
    ];

    protected $appends = [
        'total', 'start_date', 'end_date',
    ];

    public function getTotalAttribute()
    {
        return round($this->destinations()->get()->sum('subtotal'), 2);
    }

    public function getStartDateAttribute()
    {
        return $this->destinations()->get()->min('start_date');
    }

    public function getEndDateAttribute()
    {
        return $this->destinations()->get()->max('end_date');
    }

    public function getTripNameAttribute($value)
    {
        if ($value === null) {
            return implode(', ', array_column($this->destinations()->select('location')->get()->toArray(), 'location'));
        } else {
            return $value;
        }
    }

    /**
     * Get the destinations for the trip.
     */
    public function destinations()
    {
        return $this->hasMany('App\Models\Destination')->orderByRaw(DB::raw("-start_date desc"));
    }

    /**
     * Get the users for the trip.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}
