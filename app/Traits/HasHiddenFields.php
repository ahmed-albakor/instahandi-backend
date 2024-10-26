<?php

namespace App\Traits;

trait HasHiddenFields
{
    public static function getUserHiddenFields()
    {
        return [
            'email',
            'phone',
            'approve',
            'profile_setup',
            'verify_code',
            'code_expiry_date',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];
    }
}
