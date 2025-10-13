<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouse_product', function (Blueprint $t) {
            $t->id();
            $t->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->integer('stock')->default(0);
            $t->timestamps();

            $t->unique(['warehouse_id', 'product_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('warehouse_product');
    }
};
