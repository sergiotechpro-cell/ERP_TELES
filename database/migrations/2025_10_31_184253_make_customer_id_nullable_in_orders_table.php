<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para PostgreSQL, usar SQL directo es más confiable
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_customer_id_foreign');
        
        Schema::table('orders', function (Blueprint $table) {
            // Modificar la columna para permitir valores null usando SQL directo
            // Esto evita problemas con doctrine/dbal
            DB::statement('ALTER TABLE orders ALTER COLUMN customer_id DROP NOT NULL');
            
            // Recrear la foreign key con nullOnDelete
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key actual
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_customer_id_foreign');
        
        // Verificar que no haya valores null antes de hacer NOT NULL
        $nullCount = DB::table('orders')->whereNull('customer_id')->count();
        
        if ($nullCount > 0) {
            throw new \Exception("No se puede revertir: hay {$nullCount} pedidos con customer_id NULL. Asigna clientes antes de revertir.");
        }
        
        Schema::table('orders', function (Blueprint $table) {
            // Volver a hacer la columna NOT NULL
            DB::statement('ALTER TABLE orders ALTER COLUMN customer_id SET NOT NULL');
            
            // Recrear la foreign key original
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();
        });
    }
};
