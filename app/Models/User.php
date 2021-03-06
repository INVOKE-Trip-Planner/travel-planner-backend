<?php

namespace App\Models;

use App\Traits\FullTextSearch;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements Auditable, JWTSubject
{
    use Notifiable;
    use \OwenIt\Auditing\Auditable;
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
    use FullTextSearch;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'avatar', 'phone', 'gender', 'birth_date', 'last_login_at', 'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'last_login_at', 'last_login_ip', 'email_verified_at', 'pivot', 'laravel_through_key', 'relevance_score',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The columns of the full text index
     */
    protected $searchable = [
        'username', 'name', 'email',
    ];

    /**
     * Get the trips for the user.
     */
    public function trips()
    {
        return $this->belongsToMany('App\Models\Trip');
    }

    /**
     * Get the destinations for the user.
     */
    public function destinations()
    {
        return $this->hasManyDeep('App\Models\Destination', ['trip_user', 'App\Models\Trip']);
    }

    /**
     * Get the transports for the user.
     */
    public function transports()
    {
        return $this->hasManyDeep('App\Models\Transport', ['trip_user', 'App\Models\Trip', 'App\Models\Destination']);
    }

    /**
     * Get the accommodationss for the user.
     */
    public function accommodations()
    {
        return $this->hasManyDeep('App\Models\Accommodation', ['trip_user', 'App\Models\Trip', 'App\Models\Destination']);
    }

    /**
     * Get the itineraries for the user.
     */
    public function itineraries()
    {
        return $this->hasManyDeep('App\Models\Itinerary', ['trip_user', 'App\Models\Trip', 'App\Models\Destination']);
    }

    /**
     * Get the accommodation bookings.
     */
    public function acommodationBookings()
    {
        return $this->hasMany('App\Models\AccommodationBooking');
    }

    /**
     * Get the transport bookings.
     */
    public function transportBookings()
    {
        return $this->hasMany('App\Models\TransportBooking');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
