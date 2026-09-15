<?php

namespace App\Services;

use App\Models\DiscrepancyAlert;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockCount;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /** @param array<string,mixed> $before @param array<string,mixed> $after */
    public function audit(?int $userId, string $action, string $type, ?int $id, array $before = [], array $after = []): void
    {
        DB::table('activity_logs')->insert(['user_id' => $userId, 'action' => $action, 'reference_type' => $type,
            'reference_id' => $id, 'before_values' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_values' => json_encode($after, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }

    public function locked(Closure $callback): mixed
    {
        return DB::transaction(function () use ($callback): mixed {
            if (! DB::table('inventory_locks')->where('id', 1)->lockForUpdate()->first()) {
                throw new \RuntimeException('Inventory lock not installed.');
            }

            return $callback();
        }, 3);
    }

    /** @param list<string> $roles */
    public function authorize(User $user, array $roles): User
    {
        $current = User::with('role')->findOrFail($user->id);
        abort_unless($current->is_active && in_array($current->role?->name, $roles, true), 403);

        return $current;
    }

    /** @param array<string,mixed> $data @param list<string> $roles */
    private function once(User $user, string $kind, array $data, array $roles, Closure $callback): int
    {
        Validator::make($data, ['submission_key' => ['required', 'uuid']])->validate();

        return $this->locked(function () use ($user, $kind, $data, $roles, $callback): int {
            $this->authorize($user, $roles);
            $hash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
            $existing = DB::table('operations')->where('submission_key', $data['submission_key'])->first();
            if ($existing) {
                if ($existing->user_id != $user->id || $existing->kind !== $kind || $existing->payload_hash !== $hash) {
                    throw ValidationException::withMessages(['submission_key' => 'Borang ini sudah digunakan dengan maklumat lain. Buka borang baharu.']);
                }

                return (int) $existing->result_id;
            }
            $operationId = DB::table('operations')->insertGetId(['submission_key' => $data['submission_key'],
                'user_id' => $user->id, 'kind' => $kind, 'payload_hash' => $hash, 'created_at' => now(), 'updated_at' => now()]);
            $result = (int) $callback($operationId);
            DB::table('operations')->where('id', $operationId)->update(['result_id' => $result]);

            return $result;
        });
    }

    public function balance(int $productId): int
    {
        return (int) InventoryMovement::where('product_id', $productId)->sum('quantity');
    }

    public function version(int $productId): int
    {
        return (int) InventoryMovement::where('product_id', $productId)->max('id');
    }

    public static function cents(string $amount): int
    {
        if (! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $amount)) {
            throw ValidationException::withMessages(['selling_price' => 'Harga mesti nombor positif dengan maksimum dua tempat perpuluhan.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    /** @param array<string,mixed> $data */
    private function catalog(array $data, ?Product $product = null): array
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', Rule::in(['unit', 'biji', 'pek', 'kotak', 'potong'])],
            'barcode' => ['nullable', 'string', 'regex:/^[A-Za-z0-9-]{1,100}$/D', Rule::unique('products')->ignore($product?->id)],
            'selling_price' => ['required', 'regex:/^\d{1,6}(?:\.\d{1,2})?$/D'],
            'reorder_level' => ['required', 'integer', 'between:0,1000000'],
        ])->validate();
        $validated['barcode'] = ($validated['barcode'] ?? null) ?: null;
        $validated['selling_price'] = self::money(self::cents((string) $validated['selling_price']));
        if ($product && $product->unit !== $validated['unit'] && InventoryMovement::where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages(['unit' => 'Unit tidak boleh diubah selepas pergerakan stok. Daftar barang lain untuk unit baharu.']);
        }

        return $validated;
    }

    /** @param array<string,mixed> $data */
    private function newProduct(User $user, array $data): Product
    {
        $values = $this->catalog($data);
        do {
            $code = 'BRG-'.strtoupper(Str::random(8));
        } while (Product::where('sku', $code)->exists());
        $product = Product::create([...$values, 'sku' => $code, 'is_active' => true]);
        $this->audit($user->id, 'barang.didaftar', 'product', $product->id, [], $product->only(['sku', 'name', 'unit', 'barcode', 'selling_price', 'reorder_level']));

        return $product;
    }

    /** @param array<string,mixed> $data */
    public function saveProduct(User $user, array $data, ?int $id = null): int
    {
        return $this->once($user, 'catalog', $data + ['product_id' => $id], ['Owner'], function () use ($user, $data, $id): int {
            if ($id === null) {
                return $this->newProduct($user, $data)->id;
            }
            $product = Product::lockForUpdate()->findOrFail($id);
            $before = $product->only(['name', 'unit', 'barcode', 'selling_price', 'reorder_level', 'description', 'is_active']);
            $values = $this->catalog($data, $product);
            Validator::make($data, ['is_active' => ['required', 'boolean']])->validate();
            $product->update([...$values, 'is_active' => $data['is_active']]);
            $this->audit($user->id, 'barang.dikemas_kini', 'product', $id, $before, $product->only(array_keys($before)));

            return $id;
        });
    }

    private function movement(User $user, Product $product, int $quantity, string $type, string $reference, int $id, ?string $notes): InventoryMovement
    {
        $before = $this->balance($product->id);
        $after = $before + $quantity;
        if ($after < 0 || $after > 2000000000) {
            throw ValidationException::withMessages(['items' => 'Stok tidak mencukupi atau melebihi had: '.$product->name]);
        }

        return InventoryMovement::create(['product_id' => $product->id, 'user_id' => $user->id, 'movement_type' => $type,
            'quantity' => $quantity, 'quantity_before' => $before, 'quantity_after' => $after,
            'reference_type' => $reference, 'reference_id' => $id, 'notes' => $notes]);
    }

    /** @param array<string,mixed> $data */
    public function receive(User $user, array $data): int
    {
        return $this->once($user, 'receipt', $data, ['Owner'], function (int $operationId) use ($user, $data): int {
            Validator::make($data, ['items' => ['required', 'array', 'min:1', 'max:50'],
                'items.*.quantity' => ['required', 'integer', 'between:1,1000000'],
                'items.*.product_id' => ['nullable', 'integer'], 'notes' => ['nullable', 'string', 'max:1000'],
                'purpose' => ['required', Rule::in(['OPENING', 'STOCK_IN'])]])->validate();
            $seen = [];
            foreach ($data['items'] as $item) {
                $product = ! empty($item['product_id']) ? Product::lockForUpdate()->findOrFail($item['product_id']) : $this->newProduct($user, $item);
                if (isset($seen[$product->id])) {
                    throw ValidationException::withMessages(['items' => 'Gabungkan kuantiti barang yang sama dalam satu baris.']);
                }
                $seen[$product->id] = true;
                if ($data['purpose'] === 'OPENING' && InventoryMovement::where('product_id', $product->id)->exists()) {
                    throw ValidationException::withMessages(['purpose' => 'Stok permulaan hanya untuk barang tanpa sejarah pergerakan. Gunakan penerimaan biasa.']);
                }
                $this->movement($user, $product, (int) $item['quantity'], $data['purpose'], 'receipt', $operationId, $data['notes'] ?? null);
            }
            $this->audit($user->id, 'stok.diterima', 'receipt', $operationId, [], ['purpose' => $data['purpose'], 'items' => count($seen), 'notes' => $data['notes'] ?? null]);

            return $operationId;
        });
    }

    /** @param array<string,mixed> $data */
    public function sell(User $user, array $data): int
    {
        return $this->once($user, 'sale', $data, ['Owner', 'Cashier'], function () use ($user, $data): int {
            Validator::make($data, ['items' => ['required', 'array', 'min:1', 'max:50'],
                'items.*.product_id' => ['required', 'integer', 'distinct'],
                'items.*.quantity' => ['required', 'integer', 'between:1,1000000']])->validate();
            $sale = Sale::create(['transaction_no' => 'J-'.Str::ulid(), 'cashier_id' => $user->id, 'total_amount' => '0.00', 'status' => 'completed']);
            $total = 0;
            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                if (! $product->is_active) {
                    throw ValidationException::withMessages(['items' => 'Barang dihentikan tidak boleh dijual: '.$product->name]);
                }
                $subtotal = self::cents($product->selling_price) * (int) $item['quantity'];
                $total += $subtotal;
                if ($total > 9999999999) {
                    throw ValidationException::withMessages(['items' => 'Jumlah jualan melebihi had RM99,999,999.99.']);
                }
                SaleItem::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price, 'subtotal' => self::money($subtotal)]);
                $this->movement($user, $product, -(int) $item['quantity'], 'SALE', 'sale', $sale->id, null);
            }
            $sale->update(['total_amount' => self::money($total)]);
            $this->audit($user->id, 'jualan.direkod', 'sale', $sale->id, [], ['transaction_no' => $sale->transaction_no, 'total_amount' => $sale->total_amount]);

            return $sale->id;
        });
    }

    /** @param array<string,mixed> $data */
    public function withdraw(User $user, array $data): int
    {
        return $this->once($user, 'withdrawal', $data, ['Owner', 'Cashier'], function (int $operationId) use ($user, $data): int {
            Validator::make($data, ['product_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'between:1,1000000'],
                'reason' => ['required', Rule::in(['DAMAGED', 'EXPIRED', 'SAMPLE', 'OWN_USE'])], 'notes' => ['required', 'string', 'min:3', 'max:1000']])->validate();
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $movement = $this->movement($user, $product, -(int) $data['quantity'], $data['reason'], 'withdrawal', $operationId, $data['notes']);
            $this->audit($user->id, 'stok.dikeluarkan', 'inventory_movement', $movement->id, [], ['quantity' => $movement->quantity, 'reason' => $data['reason'], 'notes' => $data['notes']]);

            return $movement->id;
        });
    }

    public function countToken(User $user, int $productId): string
    {
        return Crypt::encryptString(json_encode(['product_id' => $productId, 'user_id' => $user->id,
            'version' => $this->version($productId), 'issued_at' => now()->timestamp], JSON_THROW_ON_ERROR));
    }

    /** @param array<string,mixed> $data */
    public function count(User $user, array $data): int
    {
        return $this->once($user, 'count', $data, ['Owner'], function () use ($user, $data): int {
            Validator::make($data, ['product_id' => ['required', 'integer'], 'physical_quantity' => ['required', 'integer', 'between:0,2000000000'],
                'count_token' => ['required', 'string'], 'notes' => ['nullable', 'string', 'max:1000'], 'recount_of' => ['nullable', 'integer']])->validate();
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            try {
                $token = json_decode(Crypt::decryptString($data['count_token']), true, flags: JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                throw ValidationException::withMessages(['count_token' => 'Borang kiraan tidak sah. Buka semula borang.']);
            }
            $version = $this->version($product->id);
            if (($token['product_id'] ?? null) != $product->id || ($token['user_id'] ?? null) != $user->id
                || ($token['version'] ?? null) !== $version || ($token['issued_at'] ?? 0) < now()->subHours(2)->timestamp) {
                throw ValidationException::withMessages(['count_token' => 'Borang lapuk atau stok telah berubah. Buka semula borang dan kira semula barang.']);
            }
            $parent = empty($data['recount_of']) ? null : StockCount::findOrFail($data['recount_of']);
            if ($parent && $parent->product_id != $product->id) {
                throw ValidationException::withMessages(['recount_of' => 'Kiraan semula mesti melibatkan barang yang sama.']);
            }
            $expected = $this->balance($product->id);
            $difference = (int) $data['physical_quantity'] - $expected;
            $status = $difference === 0 ? 'MATCH' : ($difference < 0 ? 'SHORTAGE' : 'SURPLUS');
            $count = StockCount::create(['product_id' => $product->id, 'user_id' => $user->id, 'expected_quantity' => $expected,
                'physical_quantity' => $data['physical_quantity'], 'discrepancy_quantity' => $difference, 'status' => $status,
                'notes' => $data['notes'] ?? null, 'movement_version' => $version, 'counted_at' => now(), 'recount_of' => $parent?->id]);
            if ($difference !== 0) {
                DiscrepancyAlert::create(['stock_count_id' => $count->id, 'product_id' => $product->id,
                    'discrepancy_quantity' => $difference, 'type' => $status, 'status' => 'OPEN']);
            }
            $this->audit($user->id, 'stok.dikira', 'stock_count', $count->id, ['expected' => $expected], ['physical' => (int) $data['physical_quantity'], 'difference' => $difference, 'recount_of' => $parent?->id]);

            return $count->id;
        });
    }

    /** @param array<string,mixed> $data */
    public function review(User $user, int $alertId, array $data): int
    {
        return $this->once($user, 'review', $data + ['alert_id' => $alertId], ['Owner'], function () use ($user, $alertId, $data): int {
            Validator::make($data, ['action' => ['required', Rule::in(['note', 'review', 'close', 'adjust'])],
                'note' => ['required', 'string', 'min:3', 'max:2000']])->validate();
            $alert = DiscrepancyAlert::lockForUpdate()->findOrFail($alertId);
            if ($alert->status === 'RESOLVED') {
                throw ValidationException::withMessages(['action' => 'Perbezaan ini sudah selesai. Bukti dan catatan asal dikekalkan.']);
            }
            $before = ['status' => $alert->status];
            if ($data['action'] === 'adjust') {
                $count = StockCount::findOrFail($alert->stock_count_id);
                $product = Product::lockForUpdate()->findOrFail($alert->product_id);
                if ($count->movement_version === null || $this->version($product->id) != $count->movement_version
                    || StockCount::where('product_id', $product->id)->where('id', '>', $count->id)->exists()
                    || $this->balance($product->id) != $count->expected_quantity) {
                    throw ValidationException::withMessages(['action' => 'Bukti lapuk: ada pergerakan atau kiraan baharu. Lakukan kiraan semula; tiada pelarasan disimpan.']);
                }
                $movement = $this->movement($user, $product, (int) $count->discrepancy_quantity, 'ADJUSTMENT', 'stock_count', $count->id, $data['note']);
                $alert->adjustment_id = $movement->id;
            }
            if (in_array($data['action'], ['close', 'adjust'], true)) {
                $alert->status = 'RESOLVED';
                $alert->resolved_by = $user->id;
                $alert->resolved_at = now();
                $alert->resolution = $data['note'];
            } elseif ($data['action'] === 'review') {
                $alert->status = 'REVIEWING';
            }
            $alert->save();
            DB::table('alert_notes')->insert(['alert_id' => $alertId, 'user_id' => $user->id, 'action' => $data['action'], 'note' => $data['note'], 'created_at' => now()]);
            $this->audit($user->id, 'perbezaan.'.$data['action'], 'alert', $alertId, $before, ['status' => $alert->status, 'note' => $data['note'], 'adjustment_id' => $alert->adjustment_id]);

            return $alertId;
        });
    }

    /** @param array<string,mixed> $data */
    public function cashier(User $owner, array $data, ?int $id = null): int
    {
        return $this->locked(function () use ($owner, $data, $id): int {
            $this->authorize($owner, ['Owner']);
            $user = $id ? User::with('role')->lockForUpdate()->findOrFail($id) : null;
            abort_if($user && $user->role?->name !== 'Cashier', 403, 'Hanya akaun Cashier boleh diurus di sini.');
            $values = Validator::make($data, ['name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'password' => [$id ? 'nullable' : 'required', 'string', 'min:12', 'max:200', 'confirmed'],
                'is_active' => ['required', 'boolean']])->validate();
            $before = $user?->only(['name', 'email', 'is_active']) ?? [];
            if (empty($values['password'])) {
                unset($values['password']);
            }
            if ($user) {
                if ($user->email !== $values['email']) {
                    $user->email_verified_at = null;
                }
                $user->fill($values)->save();
            } else {
                $user = User::create([...$values, 'role_id' => Role::firstOrCreate(['name' => 'Cashier'])->id]);
            }
            $this->audit($owner->id, $id ? 'akaun.dikemas_kini' : 'akaun.dicipta', 'user', $user->id, $before,
                $user->only(['name', 'email', 'is_active']) + ['password_changed' => isset($values['password'])]);

            return $user->id;
        });
    }
}
