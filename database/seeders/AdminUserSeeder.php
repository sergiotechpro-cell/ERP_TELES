<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Email fijo para idempotencia
        $email = 'admin@teleserp.local';

        $user = User::updateOrCreate(
            ['email' => $email], // criterio de búsqueda
            [
                'name'              => 'Administrador',
                'password'          => Hash::make('Admin#1234'), // cámbiala luego en producción
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );

        // Crear rol Manager si no existe (por si se ejecuta este seeder solo)
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        
        // Asignar rol Manager al admin (tiene acceso a todos los módulos)
        if (!$user->hasRole('Manager')) {
            $user->assignRole('Manager');
        }

        $this->command->info("Usuario admin disponible: {$user->email} / Admin#1234");
    }
}
