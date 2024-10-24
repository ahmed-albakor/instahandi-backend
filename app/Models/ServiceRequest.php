<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_requests';

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'client_id',
        'title',
        'description',
        'status',
        'payment_type',
        'estimated_hours',
        'price',
        'start_date',
        'completion_date',
        'service_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'code', 'code');
    }

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
