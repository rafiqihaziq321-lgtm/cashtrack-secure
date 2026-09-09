<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use Illuminate\Console\Command;

class BackfillInventoryHashes extends Command
{
    protected $signature = 'inventory:backfill-hashes';

    protected $description = 'Generate hash chain for existing inventory movements';

    public function handle(): int
    {
        $movements = InventoryMovement::orderBy('id')->get();

        if ($movements->isEmpty()) {
            $this->info('No inventory movements found.');
            return self::SUCCESS;
        }

        $previousHash = null;

        foreach ($movements as $movement) {
            $movement->previous_hash = $previousHash;

            $movement->record_hash = hash(
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

            $movement->saveQuietly();

            $previousHash = $movement->record_hash;
        }

        $this->info(
            $movements->count() .
            ' inventory movement(s) successfully hashed.'
        );

        return self::SUCCESS;
    }
}