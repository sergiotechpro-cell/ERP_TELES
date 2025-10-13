<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('transfers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('warehouse_from_id')->constrained('warehouses')->cascadeOnDelete();
      $t->foreignId('warehouse_to_id')->constrained('warehouses')->cascadeOnDelete();
      $t->foreignId('created_by')->constrained('users');
      $t->enum('estado',['borrador','en_transito','completado','cancelado'])->default('borrador');
      $t->timestamp('enviado_at')->nullable();
      $t->timestamp('recibido_at')->nullable();
      $t->text('nota')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('transfers'); }
};
