<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('products', function (Blueprint $t) {
      $t->decimal('precio_mayoreo', 12,2)->nullable()->after('precio_venta');
      $t->enum('price_tier', ['menudeo','mayoreo'])->default('menudeo')->after('precio_mayoreo');
    });
  }
  public function down(): void {
    Schema::table('products', function (Blueprint $t) {
      $t->dropColumn(['precio_mayoreo','price_tier']);
    });
  }
};
