<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // agrega la columna sólo si no existe
            if (!Schema::hasColumn('warranty_claims', 'status')) {
                $table->string('status', 20)->default('abierta')->after('condicion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
