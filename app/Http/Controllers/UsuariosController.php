<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $rolesPermitidos = ['admin', 'virtuality'];

        $usuarios = User::query()
            ->with('roles')
            ->whereHas('roles', fn ($r) => $r->whereIn('name', $rolesPermitidos))
            ->orderBy('name')
            ->get(); // <-- sin paginate: DataTables paginará en el cliente

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'q'        => $q, // por si quieres precargar el input desde ?q=
        ]);
    }
}
