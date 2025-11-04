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
        // En PostgreSQL, Laravel usa CHECK constraints para enums
        // Eliminar el constraint existente y crear uno nuevo con todos los estados
        
        DB::statement("
            ALTER TABLE orders 
            DROP CONSTRAINT IF EXISTS orders_estado_check
        ");
        
        // Agregar nuevo constraint con todos los estados
        // Estados existentes: capturado, preparacion, asignado, en_ruta, entregado, cancelado
        // Nuevos estados: entregado_pendiente_pago, finalizado
        DB::statement("
            ALTER TABLE orders 
            ADD CONSTRAINT orders_estado_check 
            CHECK (estado IN (
                'capturado',
                'preparacion',
                'asignado',
                'en_ruta',
                'entregado',
                'entregado_pendiente_pago',
                'finalizado',
                'cancelado'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Actualizar registros con nuevos estados a estados válidos antiguos
        DB::statement("
            UPDATE orders 
            SET estado = CASE 
                WHEN estado IN ('entregado_pendiente_pago', 'finalizado') 
                THEN 'entregado'
                ELSE estado
            END
        ");
        
        // Revertir al constraint original
        DB::statement("
            ALTER TABLE orders 
            DROP CONSTRAINT IF EXISTS orders_estado_check
        ");
        
        DB::statement("
            ALTER TABLE orders 
            ADD CONSTRAINT orders_estado_check 
            CHECK (estado IN ('capturado','preparacion','asignado','en_ruta','entregado','cancelado'))
        ");
    }
};
