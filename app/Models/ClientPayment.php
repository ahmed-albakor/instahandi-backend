<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'client_id',
        'service_request_id',
        'amount',
        'method',
        'status',
        'description',
        'payment_data',
    ];

    protected $casts = [
        'id' => 'integer',
        'client_id' => 'integer',
        'service_request_id' => 'integer',
        'amount' => 'float',
        'method' => 'string',
        'status' => 'string',
        'description' => 'string',
        'payment_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
