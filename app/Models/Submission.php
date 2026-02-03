<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $warehouse_id
 * @property int $item_id
 * @property int $staff_id
 * @property int $quantity
 * @property string $status
 * @property string $receipt_number
 * @property \Carbon\Carbon $receipt_date
 * @property string $supplier_name
 * @property string $notes
 * @property bool $is_draft
 * @property string $item_name
 */
class Submission extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'item_id',
        'item_name',
        'unit_id',
        'warehouse_id',
        'staff_id',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'supplier_id',
        'nota_number',
        'receive_date',
        'notes',
        'invoice_photo',
        'status',
        'is_draft',
        'conversion_factor',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'receive_date' => 'date',
            'submitted_at' => 'datetime',
            'is_draft' => 'boolean',
        ];
    }

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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function submissionPhotos(): HasMany
    {
        return $this->hasMany(SubmissionPhoto::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SubmissionPhoto::class);
    }

    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

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