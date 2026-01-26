<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $parent_id
 * @property string $description
 * @property bool $is_active
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Generate next code for new sub-category
     * Format: parent_code.XXX (where XXX is next number)
     */
    public function generateNextSubCategoryCode(): string
    {
        // Get last child category
        $lastChild = $this->children()
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastChild) {
            // First child, start from .001
            return $this->code . '.001';
        }

        // Extract last segment and increment
        $codeParts = explode('.', $lastChild->code);
        $lastSegment = (int) end($codeParts);
        $nextSegment = str_pad($lastSegment + 1, 3, '0', STR_PAD_LEFT);

        // Build new code
        array_pop($codeParts);
        $codeParts[] = $nextSegment;
        
        return implode('.', $codeParts);
    }

    /**
     * Get full path name (parent > child > grandchild)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get level/depth of category in hierarchy
     */
    public function getLevelAttribute(): int
    {
        return substr_count($this->code, '.');
    }

    /**
     * Check if category is a leaf (has no children)
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }
}