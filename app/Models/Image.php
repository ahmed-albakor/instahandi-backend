<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Image extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'path',
    ];

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => !is_null($value) ? asset("storage/" . $value) : null,
        );
    }
}
