<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicRequestItem extends Model
{
    protected $fillable = [
        'public_request_id',
        'item_id',
        'quantity_requested',
        'quantity_approved',
    ];

    protected $casts = [
        'quantity_requested' => 'integer',
        'quantity_approved'  => 'integer',
    ];

    public function publicRequest()
    {
        return $this->belongsTo(PublicRequest::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
