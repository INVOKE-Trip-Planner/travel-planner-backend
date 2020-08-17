<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Cost extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cost', 'costable_id', 'costable_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'id', 'costable_id', 'costable_type',
    ];

    /**
     * Get the owning costable model.
     */
    public function costable()
    {
        return $this->morphTo(__FUNCTION__, 'costable_type', 'costable_id');
    }
}
