<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // product_id (FK a products.id)
            if (!Schema::hasColumn('warranty_claims', 'product_id')) {
                $table->foreignId('product_id')->constrained('products');
            }

            // serial_number_id (FK a serial_numbers.id)
            if (!Schema::hasColumn('warranty_claims', 'serial_number_id')) {
                $table->foreignId('serial_number_id')->nullable()->constrained('serial_numbers');
            }

            // motivo (texto corto)
            if (!Schema::hasColumn('warranty_claims', 'motivo')) {
                $table->string('motivo', 255)->nullable();
            }

            // condicion (display estrellado, chasis coincide, etc.)
            if (!Schema::hasColumn('warranty_claims', 'condicion')) {
                $table->string('condicion', 120)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // IMPORTANTE: Primero eliminar FKs si existen, luego columnas
            if (Schema::hasColumn('warranty_claims', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('warranty_claims', 'serial_number_id')) {
                $table->dropForeign(['serial_number_id']);
                $table->dropColumn('serial_number_id');
            }
            if (Schema::hasColumn('warranty_claims', 'motivo')) {
                $table->dropColumn('motivo');
            }
            if (Schema::hasColumn('warranty_claims', 'condicion')) {
                $table->dropColumn('condicion');
            }
        });
    }
};
