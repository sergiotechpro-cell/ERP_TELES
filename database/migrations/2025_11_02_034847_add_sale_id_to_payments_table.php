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
        Schema::table('payments', function (Blueprint $table) {
            // Hacer order_id nullable para permitir pagos de ventas también
            $table->foreignId('order_id')->nullable()->change();
            // Agregar sale_id para pagos de ventas POS
            $table->foreignId('sale_id')->nullable()->after('order_id')->constrained('sales')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
