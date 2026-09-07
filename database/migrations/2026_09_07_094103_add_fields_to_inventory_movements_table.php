<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('movement_type', 50);

            $table->integer('quantity');

            $table->integer('quantity_before')->nullable();

            $table->integer('quantity_after')->nullable();

            $table->string('reference_type', 50)->nullable();

            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'product_id',
                'user_id',
                'movement_type',
                'quantity',
                'quantity_before',
                'quantity_after',
                'reference_type',
                'reference_id',
                'notes',
            ]);
        });
    }
};