<?php

namespace App\Models;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'code',
        'order_id',
        'service_request_id',
        'client_id',
        'price',
        'status',
        'due_date',
        'paid_at',
        'description',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'service_request_id' => 'integer',
        'client_id' => 'integer',
        'price' => 'float',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // service request
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }


    public function payments()
    {
        return $this->hasManyThrough(
            ClientPayment::class,
            ServiceRequest::class,
            'id',
            'service_request_id',
            'service_request_id',
            'id'
        );
    }
}
