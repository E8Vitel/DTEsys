<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['roles', 'permissions'])->get();

        $usuarios->each(function (User $usuario): void {
            $usuario->setAttribute(
                'all_permissions',
                $usuario->getAllPermissions()->pluck('name')->values()->all(),
            );
        });

        $roles = Role::orderBy('name')->get();
        $permisos = Permission::orderBy('name')->get();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    public function asignarRol(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);

        $user->assignRole($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Rol asignado correctamente');
    }

    public function quitarRol(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);

        $user->removeRole($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Rol removido correctamente');
    }

    public function asignarPermiso(Request $request, User $user)
    {
        $request->validate(['permission' => 'required|exists:permissions,name']);

        $user->givePermissionTo($request->permission);

        return redirect()->route('usuarios.index')->with('success', 'Permiso asignado correctamente');
    }

    public function quitarPermiso(Request $request, User $user)
    {
        $request->validate(['permission' => 'required|exists:permissions,name']);

        $user->revokePermissionTo($request->permission);

        return redirect()->route('usuarios.index')->with('success', 'Permiso removido correctamente');
    }
}