<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    const MOVEMENT_TYPE_IN = 'in';
    const MOVEMENT_TYPE_OUT = 'out';
    const MOVEMENT_TYPE_ADJUSTMENT = 'adjustment';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'reference_id');
    }

    // Scopes
    public function scopeIn($query)
    {
        return $query->where('movement_type', self::MOVEMENT_TYPE_IN);
    }

    public function scopeOut($query)
    {
        return $query->where('movement_type', self::MOVEMENT_TYPE_OUT);
    }

    public function scopeAdjustment($query)
    {
        return $query->where('movement_type', self::MOVEMENT_TYPE_ADJUSTMENT);
    }
}