<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionPhoto extends Model
{
    use HasFactory;

    const PHOTO_TYPE_NOTA = 'nota';
    const PHOTO_TYPE_ITEM_CONDITION = 'item_condition';

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'photo_type',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    // Relationships
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}