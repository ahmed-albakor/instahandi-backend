<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'name',
        'notes',
        'materials_include',
        'service_request_id',
    ];


    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'notes' => 'string',
        'materials_include' => 'boolean',
        'service_request_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
