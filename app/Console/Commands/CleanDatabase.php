<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CleanDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean 
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia todas las tablas del ERP, manteniendo solo el usuario admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que deseas limpiar TODAS las tablas del ERP? Esta acción NO se puede deshacer.')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->info('🧹 Iniciando limpieza de la base de datos...');

        try {
            DB::beginTransaction();

            // Obtener el ID del usuario admin para preservarlo
            $adminUser = User::where('email', 'admin@teleserp.local')->first();
            $adminUserId = $adminUser ? $adminUser->id : null;

            // Desactivar temporalmente las restricciones de claves foráneas
            DB::statement('SET session_replication_role = replica;');

            // Limpiar tablas en orden (respetando relaciones)
            
            // Tablas relacionadas con entregas y rutas
            $this->info('  → Limpiando entregas y rutas...');
            DB::table('delivery_schedules')->truncate();
            DB::table('delivery_assignments')->truncate();
            DB::table('route_plans')->truncate();
            DB::table('scan_logs')->truncate();

            // Tablas relacionadas con pagos
            $this->info('  → Limpiando pagos...');
            DB::table('payments')->truncate();
            DB::table('cash_closures')->truncate();

            // Tablas relacionadas con checklist
            $this->info('  → Limpiando checklist...');
            DB::table('checklist_items')->truncate();

            // Tablas relacionadas con pedidos
            $this->info('  → Limpiando pedidos...');
            DB::table('order_items')->truncate();
            DB::table('orders')->truncate();

            // Tablas relacionadas con ventas
            $this->info('  → Limpiando ventas...');
            DB::table('sale_items')->truncate();
            DB::table('sales')->truncate();

            // Tablas relacionadas con transferencias
            $this->info('  → Limpiando transferencias...');
            DB::table('transfer_items')->truncate();
            DB::table('transfers')->truncate();

            // Tablas relacionadas con inventario
            $this->info('  → Limpiando inventario...');
            DB::table('serial_numbers')->truncate();
            DB::table('warehouse_product')->truncate();
            DB::table('products')->truncate();
            DB::table('warehouses')->truncate();

            // Tablas relacionadas con empleados (excepto admin)
            $this->info('  → Limpiando empleados...');
            if ($adminUserId) {
                DB::table('employee_profiles')->where('user_id', '!=', $adminUserId)->delete();
                DB::table('users')->where('id', '!=', $adminUserId)->delete();
            } else {
                DB::table('employee_profiles')->truncate();
                DB::table('users')->truncate();
            }

            // Tablas relacionadas con clientes
            $this->info('  → Limpiando clientes...');
            DB::table('customers')->truncate();

            // Otras tablas
            $this->info('  → Limpiando otras tablas...');
            DB::table('message_logs')->truncate();
            DB::table('warranty_claims')->truncate();

            // Reactivar restricciones de claves foráneas
            DB::statement('SET session_replication_role = DEFAULT;');

            DB::commit();

            $this->newLine();
            $this->info('✅ Limpieza completada exitosamente!');
            
            if ($adminUserId) {
                $this->info("   Usuario admin preservado (ID: {$adminUserId})");
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET session_replication_role = DEFAULT;');
            
            $this->error('❌ Error durante la limpieza:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
