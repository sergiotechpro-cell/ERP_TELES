<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('delivery_schedules', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained()->cascadeOnDelete();
      $t->foreignId('courier_id')->constrained('users');
      $t->date('fecha');
      $t->time('hora')->nullable();
      $t->string('titulo')->nullable();
      $t->jsonb('meta')->nullable(); // ej: ventana_horaria, notas
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('delivery_schedules'); }
};
