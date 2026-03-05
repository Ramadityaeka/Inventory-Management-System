<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSignature extends Model
{
    protected $fillable = [
        'user_id',
        'signature_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
