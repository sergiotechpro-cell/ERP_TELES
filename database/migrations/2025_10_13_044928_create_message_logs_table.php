<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('message_logs', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
      $t->string('to');
      $t->string('provider')->default('twilio');
      $t->text('body');
      $t->string('status')->nullable();
      $t->jsonb('meta')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('message_logs'); }
};
