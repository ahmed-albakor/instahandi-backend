<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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



    public function images()
    {
        return $this->hasMany(Image::class, 'code', 'code');
    }

    public function getImages()
    {
        return $this->images->map(function ($image) {
            $image->path = asset("storage/" . $image->path);
            return $image;
        });
    }
}
