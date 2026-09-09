<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use Illuminate\Console\Command;

class VerifyInventoryHashes extends Command
{
    protected $signature = 'inventory:verify-hashes';

    protected $description = 'Verify the integrity of the inventory movement hash chain';

    public function handle(): int
    {
        $movements = InventoryMovement::orderBy('id')->get();

        if ($movements->isEmpty()) {
            $this->info('No inventory movements found.');
            return self::SUCCESS;
        }

        $previousHash = null;
        $isValid = true;

        foreach ($movements as $movement) {

            // Verify link to previous record
            if ($movement->previous_hash !== $previousHash) {
                $this->error(
                    "Chain broken at movement ID {$movement->id}: previous_hash mismatch."
                );

                $isValid = false;
            }

            // Recalculate current record hash
            $calculatedHash = hash(
                'sha256',
                implode('|', [
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
                ])
            );

            // Compare calculated hash with stored hash
            if (!hash_equals(
                (string) $movement->record_hash,
                $calculatedHash
            )) {
                $this->error(
                    "Tampering detected at movement ID {$movement->id}: record_hash mismatch."
                );

                $isValid = false;
            }

            $previousHash = $movement->record_hash;
        }

        if ($isValid) {
            $this->info('Inventory hash chain is VALID.');
            return self::SUCCESS;
        }

        $this->error('Inventory hash chain is INVALID / TAMPERED.');

        return self::FAILURE;
    }
}