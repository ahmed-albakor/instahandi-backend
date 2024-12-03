<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{

    protected $fillable = [
        'key',
        'value',
        'type',
        'allow_null',
    ];

    protected $casts = [
        'id' => 'integer',
        'key' => 'string',
        'value' => 'string',
        'type' => 'string',
        'allow_null' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
