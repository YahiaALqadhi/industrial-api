<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'logo',
        'email',
        'phone',
        'address',
        'currency',
        'auto_reply_message',

        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_iban',
        'bank_swift_code',
        'bank_address',
        'bank_payment_instructions',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::first();

        return $setting?->{$key} ?? $default;
    }
}