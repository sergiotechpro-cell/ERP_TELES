<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('checklist_items', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained()->cascadeOnDelete();
      $t->string('texto');
      $t->boolean('completado')->default(false);
      $t->timestamp('completed_at')->nullable();
      $t->foreignId('completed_by')->nullable()->constrained('users');
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('checklist_items'); }
};
