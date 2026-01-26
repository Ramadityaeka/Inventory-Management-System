<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $category_id
 * @property string $unit
 * @property string $description
 * @property bool $is_active
 * @property string|null $inactive_reason
 * @property string|null $inactive_notes
 * @property \Carbon\Carbon|null $deactivated_at
 * @property int|null $deactivated_by
 * @property int|null $replaced_by_item_id
 */
class Item extends Model
{
    use HasFactory;

    const INACTIVE_REASON_DISCONTINUED = 'discontinued';
    const INACTIVE_REASON_WRONG_INPUT = 'wrong_input';
    const INACTIVE_REASON_SEASONAL = 'seasonal';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'unit',
        'description',
        'is_active',
        'inactive_reason',
        'inactive_notes',
        'deactivated_at',
        'deactivated_by',
        'replaced_by_item_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    public function replacementItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'replaced_by_item_id');
    }

    public function replacedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'replaced_by_item_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDiscontinued($query)
    {
        return $query->where('is_active', false)
                     ->where('inactive_reason', self::INACTIVE_REASON_DISCONTINUED);
    }

    public function scopeWrongInput($query)
    {
        return $query->where('is_active', false)
                     ->where('inactive_reason', self::INACTIVE_REASON_WRONG_INPUT);
    }

    public function scopeSeasonal($query)
    {
        return $query->where('inactive_reason', self::INACTIVE_REASON_SEASONAL);
    }

    // Methods
    public function getStockMovementData(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        // Get all warehouses that have stock movements for this item
        $warehouses = $this->stockMovements()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('warehouse')
            ->get()
            ->pluck('warehouse')
            ->unique('id')
            ->sortBy('name');

        // Generate date labels
        $labels = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format('M d');
            $currentDate->addDay();
        }

        // Prepare datasets for each warehouse
        $datasets = [];
        $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#6f42c1', '#e83e8c', '#fd7e14'];

        foreach ($warehouses as $index => $warehouse) {
            $movements = $this->stockMovements()
                ->where('warehouse_id', $warehouse->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->get();

            $data = [];
            $runningTotal = 0;

            // Calculate cumulative stock for each day
            $currentDate = $startDate->copy();
            $movementIndex = 0;

            while ($currentDate <= $endDate) {
                // Add movements that occurred on or before this date
                while ($movementIndex < $movements->count() &&
                       $movements[$movementIndex]->created_at->format('Y-m-d') <= $currentDate->format('Y-m-d')) {
                    $movement = $movements[$movementIndex];
                    $runningTotal += $movement->quantity;  // All quantities are signed
                    $movementIndex++;
                }

                $data[] = max(0, $runningTotal); // Ensure non-negative
                $currentDate->addDay();
            }

            $datasets[] = [
                'label' => $warehouse->name,
                'data' => $data,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)],
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }
}
