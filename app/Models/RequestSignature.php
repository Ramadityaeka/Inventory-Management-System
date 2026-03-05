<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestSignature extends Model
{
    protected $fillable = [
        'public_request_id',
        'signer_type',
        'signer_name',
        'signature_data',
        'signed_at',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function publicRequest()
    {
        return $this->belongsTo(PublicRequest::class);
    }
}
