<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $item_id
 * @property string $name
 * @property int $conversion_factor
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ItemUnit extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'name', 'conversion_factor'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}