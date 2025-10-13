<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('sale_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
      $t->foreignId('product_id')->constrained()->cascadeOnDelete();
      $t->integer('cantidad');
      $t->decimal('precio_unitario',12,2);
      $t->decimal('costo_unitario',12,2);
      $t->jsonb('seriales')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('sale_items'); }
};
