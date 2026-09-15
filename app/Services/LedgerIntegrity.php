<?php

namespace App\Services;

use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class LedgerIntegrity
{
    public static function digest(InventoryMovement $movement): string
    {
        $values = [
            $movement->product_id, $movement->user_id, $movement->movement_type,
            $movement->quantity, $movement->quantity_before, $movement->quantity_after,
            $movement->reference_type, $movement->reference_id, $movement->notes,
            $movement->previous_hash,
        ];
        if ((int) $movement->hash_version === 1) {
            return hash('sha256', implode('|', $values));
        }

        return hash('sha256', json_encode([
            'version' => 2, 'id' => (int) $movement->id,
            'product_id' => (int) $movement->product_id, 'user_id' => (int) $movement->user_id,
            'movement_type' => $movement->movement_type, 'quantity' => (int) $movement->quantity,
            'before' => $movement->quantity_before === null ? null : (int) $movement->quantity_before,
            'after' => $movement->quantity_after === null ? null : (int) $movement->quantity_after,
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id === null ? null : (int) $movement->reference_id,
            'notes' => $movement->notes, 'previous_hash' => $movement->previous_hash,
            'created_at' => $movement->created_at->format('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** @return array{status:string,checked:int,legacy:int,unsigned:int,error:?int,head_matches:bool} */
    public function verify(): array
    {
        return DB::transaction(function (): array {
            $head = DB::table('inventory_locks')->where('id', 1)->lockForUpdate()->first();
            $previous = null;
            $lastId = null;
            $checked = $legacy = $unsigned = 0;
            $error = null;
            foreach (InventoryMovement::orderBy('id')->cursor() as $movement) {
                $checked++;
                if ((int) $movement->hash_version === 1) {
                    $legacy++;
                }
                if ($movement->record_hash === null && (int) $movement->hash_version === 1) {
                    $unsigned++;
                } elseif (! in_array((int) $movement->hash_version, [1, 2], true)
                    || $movement->previous_hash !== $previous
                    || ! hash_equals((string) $movement->record_hash, self::digest($movement))) {
                    $error ??= (int) $movement->id;
                }
                $previous = $movement->record_hash;
                $lastId = $movement->id;
            }
            $headMatches = $head && $head->last_id == $lastId && $head->last_hash === $previous;

            return ['status' => $error || ! $headMatches ? 'GAGAL' : ($unsigned ? 'TIDAK LENGKAP' : 'LULUS'),
                'checked' => $checked, 'legacy' => $legacy, 'unsigned' => $unsigned, 'error' => $error, 'head_matches' => (bool) $headMatches];
        });
    }
}
