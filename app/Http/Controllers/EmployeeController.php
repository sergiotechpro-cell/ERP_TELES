<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $empleados = EmployeeProfile::with('user')->paginate(15);
        return view('empleados.index', compact('empleados'));
    }

    public function create()
    {
        return view('empleados.create');
    }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:50'],
            'direccion' => ['required', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del empleado es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'El email ya está registrado. Por favor, usa otro email.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
        ]);

        $password = '12345678'; // Contraseña por defecto
        
        $user = User::create([
            'name' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => bcrypt($password),
        ]);

        EmployeeProfile::create([
            'user_id' => $user->id,
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
        ]);
        
        // Mostrar credenciales en el mensaje
        $credenciales = [
            'email' => $validated['email'],
            'password' => $password,
        ];
        
        return redirect()
            ->route('empleados.index')
            ->with('ok', 'Empleado creado exitosamente.')
            ->with('credenciales', $credenciales);
    }

    public function show(EmployeeProfile $empleado)
    {
        $empleado->load('user');
        return view('empleados.show', compact('empleado'));
    }

    public function edit(EmployeeProfile $empleado)
    {
        return view('empleados.edit', compact('empleado'));
    }

    public function update(Request $r, EmployeeProfile $empleado)
    {
        $validated = $r->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $empleado->user_id],
            'telefono' => ['required', 'string', 'max:50'],
            'direccion' => ['required', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del empleado es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'El email ya está registrado por otro usuario. Por favor, usa otro email.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
        ]);

        $empleado->update([
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
        ]);
        
        $empleado->user->update([
            'name' => $validated['nombre'],
            'email' => $validated['email'],
        ]);
        
        return redirect()->route('empleados.index')->with('ok','Empleado actualizado.');
    }

    public function destroy(EmployeeProfile $empleado)
    {
        $empleado->user->delete();
        $empleado->delete();
        return back()->with('ok','Empleado eliminado.');
    }
}
