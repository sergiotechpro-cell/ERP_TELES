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
        $user = User::create([
            'name'=>$r->nombre,
            'email'=>$r->email,
            'password'=>bcrypt('12345678'),
        ]);

        EmployeeProfile::create([
            'user_id'=>$user->id,
            'telefono'=>$r->telefono,
            'direccion'=>$r->direccion,
        ]);
        return redirect()->route('empleados.index')->with('ok','Empleado creado.');
    }

    public function show(EmployeeProfile $empleado)
    {
        return view('empleados.show', compact('empleado'));
    }

    public function edit(EmployeeProfile $empleado)
    {
        return view('empleados.edit', compact('empleado'));
    }

    public function update(Request $r, EmployeeProfile $empleado)
    {
        $empleado->update($r->only(['telefono','direccion']));
        $empleado->user->update(['name'=>$r->nombre,'email'=>$r->email]);
        return redirect()->route('empleados.index')->with('ok','Empleado actualizado.');
    }

    public function destroy(EmployeeProfile $empleado)
    {
        $empleado->user->delete();
        $empleado->delete();
        return back()->with('ok','Empleado eliminado.');
    }
}
