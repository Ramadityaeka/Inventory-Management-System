<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $unit_id
 * @property int $warehouse_id
 * @property int $staff_id
 * @property int $quantity
 * @property string $unit_name
 * @property int $conversion_factor
 * @property int $base_quantity
 * @property string $status
 * @property string $purpose
 * @property string|null $notes
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property string|null $received_proof_image
 * @property \Carbon\Carbon|null $received_at
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\Warehouse $warehouse
 * @property-read \App\Models\User $staff
 * @property-read \App\Models\User|null $approver
 */
class StockRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'item_id',
        'unit_id',
        'warehouse_id',
        'staff_id',
        'quantity',
        'unit_name',
        'conversion_factor',
        'base_quantity',
        'status',
        'purpose',
        'notes',
        'approved_by',
        'rejection_reason',
        'approved_at',
        'received_proof_image',
        'received_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
