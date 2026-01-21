<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $warehouse_id
 * @property int $quantity
 * @property \Carbon\Carbon $last_updated
 */
class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'quantity',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'last_updated' => 'datetime',
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
}