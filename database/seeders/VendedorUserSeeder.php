<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;

class VendedorUserSeeder extends Seeder
{
    public function run(): void
    {
        // Email fijo para idempotencia
        $email = 'vendedor@teleserp.local';

        $user = User::updateOrCreate(
            ['email' => $email], // criterio de búsqueda
            [
                'name'              => 'Vendedor',
                'password'          => Hash::make('Vendedor#1234'), // cámbiala luego en producción
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );

        // Crear rol Vendedor si no existe (por si se ejecuta este seeder solo)
        $vendedorRole = Role::firstOrCreate(['name' => 'Vendedor']);
        
        // Asignar rol Vendedor (sin acceso a dashboard, finanzas y costos)
        if (!$user->hasRole('Vendedor')) {
            $user->assignRole('Vendedor');
        }

        $this->command->info("Usuario vendedor disponible: {$user->email} / Vendedor#1234");
    }
}

