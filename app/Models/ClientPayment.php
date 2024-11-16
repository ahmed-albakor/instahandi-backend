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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
