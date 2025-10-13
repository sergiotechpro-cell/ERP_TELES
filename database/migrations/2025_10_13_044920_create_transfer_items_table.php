<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('transfer_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('transfer_id')->constrained()->cascadeOnDelete();
      $t->foreignId('product_id')->constrained()->cascadeOnDelete();
      $t->integer('cantidad');
      $t->jsonb('seriales')->nullable(); // lista de números de serie
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('transfer_items'); }
};
