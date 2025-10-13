<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('telefono')->nullable();
            $t->string('direccion')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('employee_profiles');
    }
};
