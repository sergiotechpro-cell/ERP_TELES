<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Role, Permission};

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear todos los permisos
        $perms = [
            'ver-inventario','editar-inventario',
            'ver-pedidos','editar-pedidos',
            'ver-finanzas','editar-finanzas',
            'ver-costos','editar-costos',
            'ver-dashboard',
            'ver-clientes','editar-clientes',
            'ver-empleados','editar-empleados',
            'ver-pos','editar-pos',
            'ver-bodegas','editar-bodegas',
            'ver-calendario','editar-calendario',
            'ver-rutas','editar-rutas'
        ];
        
        foreach($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Crear rol Manager con todos los permisos
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions(Permission::all());

        // Crear rol Vendedor sin acceso a finanzas, costos y dashboard
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->syncPermissions([
            'ver-inventario','editar-inventario',
            'ver-pedidos','editar-pedidos',
            'ver-clientes','editar-clientes',
            'ver-pos','editar-pos',
            'ver-bodegas','editar-bodegas',
            'ver-calendario','editar-calendario',
            'ver-rutas','editar-rutas'
        ]);

        $this->command->info('Roles y permisos creados correctamente.');
    }
}

