<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionApproval extends Model
{
    use HasFactory;

    protected $table = 'approvals';
    
    protected $fillable = [
        'submission_id',
        'admin_id',
        'action',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}