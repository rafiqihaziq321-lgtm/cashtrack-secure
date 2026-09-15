<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('unit', 30)->default('unit');
        });
        Schema::create('inventory_locks', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedBigInteger('last_id')->nullable();
            $table->string('last_hash', 64)->nullable();
        });
        $last = DB::table('inventory_movements')->orderByDesc('id')->first();
        DB::table('inventory_locks')->insert(['id' => 1, 'last_id' => $last?->id, 'last_hash' => $last?->record_hash]);
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->unsignedSmallInteger('hash_version')->default(1);
        });
        Schema::create('operations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('kind', 30);
            $table->string('payload_hash', 64);
            $table->unsignedBigInteger('result_id')->nullable();
            $table->timestamps();
        });
        Schema::table('stock_counts', function (Blueprint $table): void {
            $table->unsignedBigInteger('movement_version')->nullable();
            $table->foreignId('recount_of')->nullable()->constrained('stock_counts')->restrictOnDelete();
            $table->timestamp('counted_at')->nullable();
        });
        Schema::table('discrepancy_alerts', function (Blueprint $table): void {
            $table->foreignId('resolved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('adjustment_id')->nullable()->unique()->constrained('inventory_movements')->restrictOnDelete();
        });
        Schema::create('alert_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alert_id')->constrained('discrepancy_alerts')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->text('note');
            $table->timestamp('created_at');
        });
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action', 60);
            $table->string('reference_type', 40);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Plan B preserves audit evidence. Restore a verified backup instead of rolling this migration back.');
    }
};
