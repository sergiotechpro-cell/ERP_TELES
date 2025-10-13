<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sales', function (Blueprint $t) {
      $t->id();
      $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
      $t->foreignId('user_id')->constrained('users'); // cajero
      $t->decimal('subtotal',12,2)->default(0);
      $t->decimal('envio',12,2)->default(0);
      $t->decimal('total',12,2)->default(0);
      $t->enum('status',['abierta','pagada','cancelada'])->default('abierta');
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('sales'); }
};
