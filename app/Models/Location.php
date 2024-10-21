<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'code',
        'street_address',
        'exstra_address',
        'country',
        'city',
        'state',
        'zip_code',
    ];
}
