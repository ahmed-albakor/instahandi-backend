<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'phone',
        'description',
        'profile_photo',
        'approve',
        'profile_setup',
        'verify_code',
        'code_expiry_date',
        'email_verified_at'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function vendors()
    {
        return $this->hasOne(Vendor::class);
    }

    public function clients()
    {
        return $this->hasOne(Client::class);
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class, 'user_id');
    }

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class, 'admin_id');
    }

    public function clientPayments()
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function systemRatings()
    {
        return $this->hasMany(SystemReview::class);
    }

    public function userNotification()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }
}
