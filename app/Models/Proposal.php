<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'service_request_id',
        'vendor_id',
        'message',
        'estimated_hours',
        'price',
        'status',
        'payment_type',
    ];

    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'service_request_id' => 'integer',
        'vendor_id' => 'integer',
        'message' => 'string',
        'estimated_hours' => 'string',
        'price' => 'float',
        'status' => 'string',
        'payment_type' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
