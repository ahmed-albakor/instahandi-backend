<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'code',
        'account_type',
        'status',
        'years_experience',
        'longitude',
        'latitude',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'user_id' => 'integer',
        'account_type' => 'string',
        'status' => 'string',
        'years_experience' => 'integer',
        'longitude' => 'string',
        'latitude' => 'string',
        'has_crew' => 'boolean',
        'crew_members' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function vendorServices()
    {
        return $this->hasMany(VendorService::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(VendorReview::class);
    }


    public function getAverageRatingAttribute()
    {
        return number_format($this->reviews->avg('rating'), 1) ?? 0.0;
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'vendor_services', 'vendor_id', 'service_id');
    }
}
