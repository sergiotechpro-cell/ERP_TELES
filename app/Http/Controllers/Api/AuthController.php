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

        if (!$user || !Hash::check($r->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Verificar que el usuario sea un empleado
        $employeeProfile = $user->employeeProfile;
        if (!$employeeProfile) {
            throw ValidationException::withMessages([
                'email' => ['El usuario no es un empleado/chofer.'],
            ]);
        }

        $token = $user->createToken('courier-app')->plainTextToken;

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

