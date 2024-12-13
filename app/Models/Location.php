<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];
    
    protected $fillable = [
        'code',
        'street_address',
        'exstra_address',
        'country',
        'city',
        'state',
        'zip_code',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'street_address' => 'string',
        'exstra_address' => 'string',
        'country' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip_code' => 'string',
        'longitude' => 'string',
        'latitude' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
}
