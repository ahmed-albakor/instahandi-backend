<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorReview extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'vendor_id',
        'client_id',
        'order_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'vendor_id' => 'integer',
        'client_id' => 'integer',
        'rating' => 'integer',
        'review' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
