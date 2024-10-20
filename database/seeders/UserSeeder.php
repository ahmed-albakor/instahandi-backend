<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'code' => 'USR001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '123456789',
            'description' => 'Administrator account',
            'profile_photo' => null,
            'approve' => true,
            'verify_code' => null,
            'code_expiry_date' => null,
            'email_verified_at' => now(),
        ]);

        $vendorUser = User::create([
            'code' => 'USR002',
            'first_name' => 'Vendor',
            'last_name' => 'User',
            'email' => 'vendor@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'phone' => '987654321',
            'description' => 'Vendor account',
            'profile_photo' => null,
            'approve' => true,
            'verify_code' => null,
            'code_expiry_date' => null,
            'email_verified_at' => now(),
        ]);

        Vendor::create([
            'user_id' => $vendorUser->id,
            'code' => 'VND001',
            'account_type' => 'Individual',
            'years_experience' => 5,
            'longitude' => '35.6892',
            'latitude' => '51.3890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clientUser = User::create([
            'code' => 'USR003',
            'first_name' => 'Client',
            'last_name' => 'User',
            'email' => 'client@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '112233445',
            'description' => 'Client account',
            'profile_photo' => null,
            'approve' => true,
            'verify_code' => null,
            'code_expiry_date' => null,
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $clientUser->id,
            'code' => 'CLT001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
