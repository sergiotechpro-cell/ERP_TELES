<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delivery_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('courier_id')->references('id')->on('users');
            $t->timestamp('asignado_at');
            $t->timestamp('salida_at')->nullable();
            $t->timestamp('entregado_at')->nullable();
            $t->enum('estado', ['pendiente','en_ruta','entregado','devuelto'])->default('pendiente');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('delivery_assignments');
    }
};
