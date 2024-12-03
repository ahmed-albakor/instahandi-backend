<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'message',
        'rating',
        'client_name',
        'job',
        'profile_photo',
        'admin_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'message' => 'string',
        'rating' => 'integer',
        'client_name' => 'string',
        'job' => 'string',
        'profile_photo' => 'string',
        'admin_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];



    protected function profilePhoto(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => !is_null($value) ? asset("storage/" . $value) : null,
        );
    }
}
