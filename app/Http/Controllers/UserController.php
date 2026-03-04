<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->get();
        $roles = Role::all();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
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
}