<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\{Role, Permission};
use App\Models\User;

class RoleController extends Controller
{
  public function seed(){
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
    foreach($perms as $p) Permission::firstOrCreate(['name'=>$p]);

    // Crear rol Manager con todos los permisos
    $manager = Role::firstOrCreate(['name'=>'Manager']);
    $manager->syncPermissions(Permission::all());

    // Crear rol Vendedor sin acceso a finanzas, costos y dashboard
    $vendedor = Role::firstOrCreate(['name'=>'Vendedor']);
    $vendedor->syncPermissions([
      'ver-inventario','editar-inventario',
      'ver-pedidos','editar-pedidos',
      'ver-clientes','editar-clientes',
      'ver-pos','editar-pos',
      'ver-bodegas','editar-bodegas',
      'ver-calendario','editar-calendario',
      'ver-rutas','editar-rutas'
    ]);

    // Mantener otros roles existentes (opcional)
    $director = Role::firstOrCreate(['name'=>'Director']);
    $director->syncPermissions(Permission::all());
    
    $cajero   = Role::firstOrCreate(['name'=>'Cajero']);
    $cajero->syncPermissions(['ver-finanzas','editar-finanzas','ver-pos','editar-pos','ver-pedidos']);
    
    $mensajero= Role::firstOrCreate(['name'=>'Mensajero']);
    $mensajero->syncPermissions(['ver-pedidos']);

    return 'Roles y permisos sembrados. Manager y Vendedor configurados.';
  }

  public function assign(Request $r){
    $user = User::findOrFail($r->user_id);
    $user->syncRoles([$r->role]);
    return back()->with('ok','Rol asignado a '.$user->name);
  }
}
