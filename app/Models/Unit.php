<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $location
 * @property string $address
 * @property string $pic_name
 * @property string $pic_phone
 * @property bool $is_active
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'address',
        'pic_name',
        'pic_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'unit_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'unit_id');
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_unit_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_unit_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function getTotalItemsAttribute(): int
    {
        return $this->stocks()->distinct('item_id')->count('item_id');
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }
}
