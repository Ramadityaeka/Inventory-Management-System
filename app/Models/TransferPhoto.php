<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferPhoto extends Model
{
    use HasFactory;

    const PHOTO_TYPE_PACKING = 'packing';
    const PHOTO_TYPE_SHIPPING = 'shipping';
    const PHOTO_TYPE_RECEIVING = 'receiving';
    const PHOTO_TYPE_DAMAGE = 'damage';
    const PHOTO_TYPE_OTHER = 'other';

    public $timestamps = false;

    protected $fillable = [
        'transfer_id',
        'photo_type',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
        'uploaded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    // Relationships
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}