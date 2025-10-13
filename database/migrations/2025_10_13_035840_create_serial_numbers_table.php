<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('serial_numbers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_product_id')->constrained('warehouse_product')->cascadeOnDelete();
            $t->string('numero_serie')->unique();
            $t->enum('estado', ['disponible', 'apartado', 'entregado'])->default('disponible');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('serial_numbers');
    }
};
