<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login del chofer
     */
    public function login(Request $r)
    {
        $r->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $r->email)->first();

        if (!$user) {
            \Log::warning('Login fallido: Usuario no encontrado', ['email' => $r->email]);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!Hash::check($r->password, $user->password)) {
            \Log::warning('Login fallido: Contraseña incorrecta', ['email' => $r->email, 'user_id' => $user->id]);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Verificar que el usuario sea un empleado
        $employeeProfile = $user->employeeProfile;
        if (!$employeeProfile) {
            \Log::warning('Login fallido: Usuario sin employeeProfile', ['email' => $r->email, 'user_id' => $user->id]);
            throw ValidationException::withMessages([
                'email' => ['El usuario no es un empleado/chofer. Debe tener un perfil de empleado creado en el ERP.'],
            ]);
        }

        $token = $user->createToken('courier-app')->plainTextToken;

        \Log::info('Login exitoso', ['email' => $r->email, 'user_id' => $user->id]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $employeeProfile->telefono,
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Perfil del chofer autenticado
     */
    public function profile(Request $r)
    {
        $user = $r->user();
        $employeeProfile = $user->employeeProfile;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $employeeProfile?->telefono,
                'direccion' => $employeeProfile?->direccion,
            ]
        ]);
    }
}

