<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreignId('parent_warehouse_id')->nullable()->after('id')
                ->constrained('warehouses')->nullOnDelete()
                ->comment('Bodega padre (null = bodega principal)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['parent_warehouse_id']);
            $table->dropColumn('parent_warehouse_id');
        });
    }
};
