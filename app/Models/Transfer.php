<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $transfer_number
 * @property int $item_id
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property int $quantity
 * @property string $reason
 * @property string $status
 * @property int $requested_by
 * @property \Carbon\Carbon $requested_at
 * @property \Carbon\Carbon $approved_at
 * @property \Carbon\Carbon $shipped_at
 * @property \Carbon\Carbon $received_at
 * @property string $shipping_note
 * @property string $rejection_reason
 */
class Transfer extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_WAITING_REVIEW = 'waiting_review';
    const STATUS_WAITING_APPROVAL = 'waiting_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_WAITING_RECEIVE = 'waiting_receive';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transfer_number',
        'item_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'reason',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'received_by',
        'status',
        'rejection_stage',
        'rejection_reason',
        'requested_at',
        'reviewed_at',
        'approved_at',
        'shipped_at',
        'received_at',
        'completed_at',
        'notes',
        'shipping_note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'shipped_at' => 'datetime',
            'received_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transferPhotos(): HasMany
    {
        return $this->hasMany(TransferPhoto::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeWaitingReview($query)
    {
        return $query->where('status', self::STATUS_WAITING_REVIEW);
    }

    public function scopeWaitingApproval($query)
    {
        return $query->where('status', self::STATUS_WAITING_APPROVAL);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', self::STATUS_IN_TRANSIT);
    }

    public function scopeWaitingReceive($query)
    {
        return $query->where('status', self::STATUS_WAITING_RECEIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
}