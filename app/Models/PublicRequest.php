<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublicRequest extends Model
{
    protected $fillable = [
        'request_code',
        'token',
        'requester_name',
        'warehouse_id',
        'pic_user_id',
        'notes',
        'status',
        'rejection_reason',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_PARTIAL   = 'partial';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_COMPLETED = 'completed';

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function items()
    {
        return $this->hasMany(PublicRequestItem::class);
    }

    public function signatures()
    {
        return $this->hasMany(RequestSignature::class);
    }

    public function requesterSignature()
    {
        return $this->hasOne(RequestSignature::class)
                    ->where('signer_type', 'requester');
    }

    public function picSignature()
    {
        return $this->hasOne(RequestSignature::class)
                    ->where('signer_type', 'pic');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
