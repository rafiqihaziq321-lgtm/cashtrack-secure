<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCount extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'expected_quantity',
        'physical_quantity',
        'discrepancy_quantity',
        'status',
        'notes',
        'movement_version',
        'recount_of',
        'counted_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
