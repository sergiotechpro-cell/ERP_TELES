<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->enum('forma_pago', ['efectivo','tarjeta','transferencia']);
            $t->decimal('monto', 12, 2);
            $t->enum('estado', ['reportado','en_caja','depositado'])->default('reportado');
            $t->foreignId('held_by')->nullable()->references('id')->on('users'); // mensajero temporal
            $t->timestamp('reportado_at')->nullable();
            $t->timestamp('entregado_caja_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
