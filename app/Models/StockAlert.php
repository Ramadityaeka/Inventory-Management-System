<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    const ALERT_TYPE_LOW_STOCK = 'low_stock';
    const ALERT_TYPE_OUT_OF_STOCK = 'out_of_stock';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'alert_type',
        'current_stock',
        'threshold',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'integer',
            'threshold' => 'integer',
            'is_read' => 'boolean',
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

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->where('alert_type', self::ALERT_TYPE_LOW_STOCK);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('alert_type', self::ALERT_TYPE_OUT_OF_STOCK);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}