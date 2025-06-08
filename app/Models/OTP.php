<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['phone_number', 'otp'];

    public $timestamps = ['created_at'];

    const UPDATED_AT = null;

    // OTP expires after 5 min
    public function isExpired()
    {
        return $this->created_at->diffInMinutes(now()) > 5;
    }
}
