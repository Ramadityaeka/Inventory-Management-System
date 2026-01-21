<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';

    public $timestamps = true;
    
    // Disable any observers that might interfere
    protected static $unguarded = false;

    protected $fillable = [
        'submission_id',
        'admin_id',
        'action',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // Relationships
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}