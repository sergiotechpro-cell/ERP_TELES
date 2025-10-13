<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cash_closures', function (Blueprint $t) {
            $t->id();
            $t->enum('tipo', ['diario','semanal']);
            $t->date('fecha_corte');
            $t->decimal('total_efectivo', 12, 2)->default(0);
            $t->decimal('total_tarjeta', 12, 2)->default(0);
            $t->decimal('total_transferencia', 12, 2)->default(0);
            $t->jsonb('resumen')->nullable(); // utilidades, costos, etc.
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cash_closures');
    }
};
