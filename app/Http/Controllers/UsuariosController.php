<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $rolesPermitidos = ['admin', 'virtuality'];

        $usuarios = User::query()
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->with('roles')
            ->whereHas('roles', fn ($r) => $r->whereIn('name', $rolesPermitidos))
            ->orderBy('name')
            ->get(); // DataTables paginará en el cliente

        return view('usuarios.index', compact('usuarios', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            'roles'    => ['nullable','array'],
            'roles.*'  => [Rule::in(['admin','virtuality'])],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return back()->with('ok', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($usuario->id)],
            'password' => ['nullable','confirmed','min:8'],
            'roles'    => ['nullable','array'],
            'roles.*'  => [Rule::in(['admin','virtuality'])],
        ]);

        $usuario->name  = $data['name'];
        $usuario->email = $data['email'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        // Si se envía roles[], reemplaza; si no se envía, conserva
        if ($request->has('roles')) {
            $usuario->syncRoles($data['roles'] ?? []);
        }

        return back()->with('ok', 'Usuario actualizado.');
    }
}
