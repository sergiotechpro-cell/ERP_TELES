<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('route_plans', function (Blueprint $t) {
      $t->id();
      $t->foreignId('order_id')->constrained()->cascadeOnDelete();
      $t->jsonb('waypoints')->nullable(); // [{lat,lng}, ...]
      $t->jsonb('polyline')->nullable();  // overview polyline
      $t->integer('distance_m')->nullable();
      $t->integer('duration_s')->nullable();
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('route_plans'); }
};
