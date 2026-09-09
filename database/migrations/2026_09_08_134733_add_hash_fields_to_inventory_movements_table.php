<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('previous_hash', 64)->nullable()->after('notes');
            $table->string('record_hash', 64)->nullable()->after('previous_hash');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn([
                'previous_hash',
                'record_hash',
            ]);
        });
    }
};