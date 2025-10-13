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
      'ver-clientes','editar-clientes',
      'ver-empleados','editar-empleados',
      'ver-pos','editar-pos'
    ];
    foreach($perms as $p) Permission::firstOrCreate(['name'=>$p]);

    $director = Role::firstOrCreate(['name'=>'Director']);
    $cajero   = Role::firstOrCreate(['name'=>'Cajero']);
    $mensajero= Role::firstOrCreate(['name'=>'Mensajero']);
    $vendedor = Role::firstOrCreate(['name'=>'Vendedor']);

    $director->syncPermissions(Permission::all());
    $cajero->syncPermissions(['ver-finanzas','editar-finanzas','ver-pos','editar-pos','ver-pedidos']);
    $vendedor->syncPermissions(['ver-pedidos','editar-pedidos','ver-clientes','editar-clientes']);
    $mensajero->syncPermissions(['ver-pedidos']);

    return 'Roles y permisos sembrados.';
  }

  public function assign(Request $r){
    $user = User::findOrFail($r->user_id);
    $user->syncRoles([$r->role]);
    return back()->with('ok','Rol asignado a '.$user->name);
  }
}
