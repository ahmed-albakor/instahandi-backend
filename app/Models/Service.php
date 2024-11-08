<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Service extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'name',
        'description',
        'main_image',
    ];

    protected function mainImage(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) =>  asset("storage/" . $value),
        );
    }


    public function images()
    {
        return $this->hasMany(Image::class, 'code', 'code');
    }
}
