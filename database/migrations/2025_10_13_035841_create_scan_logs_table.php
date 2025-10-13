<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('scan_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('serial_number_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained(); // quién escanea
            $t->enum('tipo', ['salida_bodega','entrega_cliente']);
            $t->timestamp('scanned_at');
            $t->jsonb('meta')->nullable(); // foto evidencia, GPS, modelo
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('scan_logs');
    }
};
