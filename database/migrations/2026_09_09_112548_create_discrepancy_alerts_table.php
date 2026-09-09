<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discrepancy_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_count_id')
                ->constrained('stock_counts')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->integer('discrepancy_quantity');

            $table->string('type', 30);

            $table->string('status', 30)
                ->default('OPEN');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discrepancy_alerts');
    }
};