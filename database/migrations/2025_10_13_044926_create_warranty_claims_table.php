<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('warranty_claims', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained()->cascadeOnDelete();
      $t->foreignId('serial_number_id')->constrained()->cascadeOnDelete();
      $t->enum('estado',['abierta','aprobada','rechazada','cerrada'])->default('abierta');
      $t->date('fecha_compra');
      $t->text('motivo');
      $t->jsonb('evidencia')->nullable(); // fotos, notas
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('warranty_claims'); }
};
