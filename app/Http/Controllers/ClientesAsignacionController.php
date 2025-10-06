<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientesAsignacionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        
        $clientes = DB::table('clientes')
            ->select([
                'id_cliente',
                'nombre_cliente',
                'nombre_empresa',
                'direccion',
                'responsable',
                'rfc',
                'imagen',
            ])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sq) use ($q) {
                    $sq->where('rfc', 'like', "%$q%")
                       ->orWhere('nombre_cliente', 'like', "%$q%")
                       ->orWhere('nombre_empresa', 'like', "%$q%");
                });
            })
            ->orderBy('nombre_cliente')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.seleccion', compact('clientes', 'q'));
    }
}
