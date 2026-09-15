<?php

namespace App\Models;

use App\Services\LedgerIntegrity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class InventoryMovement extends Model
{
    protected $fillable = ['product_id', 'user_id', 'movement_type', 'quantity', 'quantity_before', 'quantity_after', 'reference_type', 'reference_id', 'notes'];

    protected static function booted(): void
    {
        static::creating(function (InventoryMovement $movement): void {
            if (DB::transactionLevel() < 1) {
                throw new \LogicException('Inventory movements require a transaction.');
            }
            $head = DB::table('inventory_locks')->where('id', 1)->lockForUpdate()->first();
            $last = self::orderByDesc('id')->first();
            if (! $head || $head->last_id != $last?->id || $head->last_hash !== $last?->record_hash) {
                throw new \RuntimeException('Ledger head mismatch; investigate integrity before writing.');
            }
            $movement->hash_version = 2;
            $movement->previous_hash = $head->last_hash;
            $movement->created_at = now()->startOfSecond();
            $movement->updated_at = $movement->created_at;
        });
        static::created(function (InventoryMovement $movement): void {
            $movement->record_hash = LedgerIntegrity::digest($movement);
            DB::table('inventory_movements')->where('id', $movement->id)->update(['record_hash' => $movement->record_hash]);
            DB::table('inventory_locks')->where('id', 1)->update(['last_id' => $movement->id, 'last_hash' => $movement->record_hash]);
            $movement->syncOriginal();
        });
        static::updating(fn () => throw new \LogicException('Create a compensating movement; do not edit history.'));
        static::deleting(fn () => throw new \LogicException('Do not delete inventory history.'));
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
