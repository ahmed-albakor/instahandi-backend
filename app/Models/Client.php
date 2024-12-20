<?php

namespace App\Models;

use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $hidden = ['deleted_at'];


    protected $fillable = [
        'user_id',
        'code',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'code' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    }


    public function vendorReviews()
    {
        return $this->hasMany(VendorReview::class);
    }
}
