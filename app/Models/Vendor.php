<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'account_type',
        'years_experience',
        'longitude',
        'latitude',
        'created_at',
        'updated_at'
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

    public function location()
    {
        return $this->hasOne(Location::class, 'code', 'code');
    }
}
