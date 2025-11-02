<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En PostgreSQL, Laravel usa CHECK constraints para enums
        // Necesitamos eliminar el constraint, agregar el nuevo valor y recrearlo
        
        // 1. Eliminar el constraint existente
        DB::statement("
            ALTER TABLE payments 
            DROP CONSTRAINT IF EXISTS payments_estado_check
        ");
        
        // 2. Agregar nuevo constraint con 'completado' incluido
        DB::statement("
            ALTER TABLE payments 
            ADD CONSTRAINT payments_estado_check 
            CHECK (estado IN ('reportado', 'en_caja', 'depositado', 'completado'))
        ");
    }

    public function down(): void
    {
        // Actualizar registros con 'completado' a 'depositado' antes de eliminar
        DB::statement("
            UPDATE payments 
            SET estado = 'depositado' 
            WHERE estado = 'completado'
        ");
        
        // Revertir al constraint original
        DB::statement("
            ALTER TABLE payments 
            DROP CONSTRAINT IF EXISTS payments_estado_check
        ");
        
        DB::statement("
            ALTER TABLE payments 
            ADD CONSTRAINT payments_estado_check 
            CHECK (estado IN ('reportado', 'en_caja', 'depositado'))
        ");
    }
};
