<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'movement_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'previous_hash',
        'record_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (InventoryMovement $movement) {
            $lastMovement = self::latest('id')->first();

            $movement->previous_hash = $lastMovement?->record_hash;

            $movement->record_hash = hash(
                'sha256',
                self::generateHashData($movement)
            );
        });
    }

    private static function generateHashData(
        InventoryMovement $movement
    ): string {
        return implode('|', [
            $movement->product_id,
            $movement->user_id,
            $movement->movement_type,
            $movement->quantity,
            $movement->quantity_before,
            $movement->quantity_after,
            $movement->reference_type,
            $movement->reference_id,
            $movement->notes,
            $movement->previous_hash,
        ]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}