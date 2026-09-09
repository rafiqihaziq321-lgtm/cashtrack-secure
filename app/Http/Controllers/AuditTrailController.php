<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(): View
    {
        $movements = InventoryMovement::with([
            'product',
            'user'
        ])
            ->latest('id')
            ->get();

        $orderedMovements = InventoryMovement::orderBy('id')->get();

        $previousHash = null;
        $chainValid = true;

        foreach ($orderedMovements as $movement) {

            if ($movement->previous_hash !== $previousHash) {
                $chainValid = false;
                break;
            }

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

            if (!hash_equals(
                (string) $movement->record_hash,
                $calculatedHash
            )) {
                $chainValid = false;
                break;
            }

            $previousHash = $movement->record_hash;
        }

        return view('owner.audit.index', compact(
            'movements',
            'chainValid'
        ));
    }
}