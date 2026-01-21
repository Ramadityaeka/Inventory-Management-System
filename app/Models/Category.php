<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $is_active
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code_prefix',
        'description',
        'is_active',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}