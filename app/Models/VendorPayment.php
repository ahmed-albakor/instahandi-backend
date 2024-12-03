<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPayment extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'code',
        'vendor_id',
        'order_id',
        'amount',
        'method',
        'status',
        'description',
        'payment_data',
        'user_id',
    ];


    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'vendor_id' => 'integer',
        'order_id' => 'integer',
        'amount' => 'float',
        'method' => 'string',
        'status' => 'string',
        'description' => 'string',
        'payment_data' => 'array',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];



    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
