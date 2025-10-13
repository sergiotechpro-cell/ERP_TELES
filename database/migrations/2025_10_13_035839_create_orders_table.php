<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->foreignId('created_by')->references('id')->on('users');
            $t->enum('estado', ['capturado','preparacion','asignado','en_ruta','entregado','cancelado'])->default('capturado');
            $t->string('direccion_entrega');
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->decimal('costo_envio', 10, 2)->default(0);
            $t->jsonb('ruta_sugerida')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
