<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'service_request_id', // 
        'proposal_id',  // 
        'status',
        'title',
        'description',
        'vendor_id', // 
        'price',
        'payment_type',
        'works_hours',
        'start_date',
        'completion_date',
        'created_at',
        'updated_at',
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function workLocation()
    {
        return $this->hasOneThrough(
            Location::class,
            ServiceRequest::class,
            'id',
            'code',
            'service_request_id',
            'code'
        );
    }

    public function images()
    {
        return $this->hasManyThrough(
            Image::class,
            ServiceRequest::class,
            'id',
            'code',
            'service_request_id',
            'code'
        );
    }
}
