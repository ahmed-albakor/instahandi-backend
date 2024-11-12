<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorService extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vendor_id',
        'service_id',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
