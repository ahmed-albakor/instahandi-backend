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
        'can_job',
        'start_date',
        'completion_date',
        'service_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'client_id' => 'integer',
        'service_id' => 'integer',
        'price' => 'float',
        'can_job' => 'boolean',
        'estimated_hours' => 'string',
        'start_date' => 'datetime',
        'completion_date' => 'datetime',
        'status' => 'string',
        'payment_type' => 'string',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'code', 'code');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'code', 'code');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
