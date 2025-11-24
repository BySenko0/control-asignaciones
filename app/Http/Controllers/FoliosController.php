<?php

namespace App\Http\Controllers;

use App\Models\ClientesAsignacion;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoliosController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $clienteId = $request->integer('cliente_id') ?: null;
        $q = trim((string) $request->get('q', ''));

        $folios = Solicitud::with(['cliente:id,nombre_cliente', 'asignado:id,name'])
            ->where('estado', Solicitud::FINALIZADO)
            ->when($clienteId, fn($query) => $query->where('cliente_id', $clienteId))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('dispositivo', 'like', "%{$q}%")
                        ->orWhere('modelo', 'like', "%{$q}%")
                        ->orWhere('no_serie', 'like', "%{$q}%")
                        ->orWhere('tipo_servicio', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                });
            })
            ->when(!$isAdmin, fn($query) => $query->where('asignado_a', $user->id))
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $clientes = ClientesAsignacion::orderBy('nombre_cliente')->get(['id', 'nombre_cliente']);

        return view('folios.index', compact('folios', 'clientes', 'clienteId', 'q', 'isAdmin'));
    }
}
