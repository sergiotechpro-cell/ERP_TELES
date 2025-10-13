<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('customers', function (Blueprint $t) {
      $t->enum('tipo', ['persona','empresa'])->default('persona')->after('es_empresa');
      // es_empresa queda como compatibilidad; podemos usar 'tipo'
    });
  }
  public function down(): void {
    Schema::table('customers', function (Blueprint $t) {
      $t->dropColumn('tipo');
    });
  }
};
