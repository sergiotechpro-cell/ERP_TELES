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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('commission_amount', 12, 2)->default(0)->after('total')->comment('Monto de comisión calculada para el vendedor');
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('commission_amount')->comment('Porcentaje de comisión aplicado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'commission_percentage']);
        });
    }
};
