<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('descripcion');
            $t->decimal('costo_unitario', 12, 2);
            $t->decimal('precio_venta', 12, 2);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};
