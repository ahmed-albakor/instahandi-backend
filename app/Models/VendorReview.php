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
}
