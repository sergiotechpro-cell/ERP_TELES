<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

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
                // 'role' => 'admin', // si tu tabla/users tiene columna role
            ]
        );

        $this->command->info("Usuario admin disponible: {$user->email} / Admin#1234");
    }
}
