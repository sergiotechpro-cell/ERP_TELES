<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->boolean('es_empresa')->default(false);
            $t->string('telefono')->nullable();
            $t->string('direccion_entrega')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('customers');
    }
};
