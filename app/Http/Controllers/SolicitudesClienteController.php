<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Solicitud;
use App\Models\ClientesAsignacion;
use App\Models\Plantilla;

class SolicitudesClienteController extends Controller
{
    public function index(Request $request, ?ClientesAsignacion $cliente = null)
    {
        $q         = trim((string) $request->get('q', ''));
        $clienteId = $cliente?->id;

        $solicitudes = Solicitud::with(['cliente','asignado','plantilla'])
            ->when($clienteId, fn($qq) => $qq->where('cliente_id', $clienteId))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('no_serie','like',"%{$q}%")
                      ->orWhere('dispositivo','like',"%{$q}%")
                      ->orWhere('modelo','like',"%{$q}%")
                      ->orWhere('tipo_servicio','like',"%{$q}%")
                      ->orWhere('estado','like',"%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Solo carga la lista de usuarios si el que visita es admin
        $usuarios = Auth::user()?->hasRole('admin')
            ? User::role(['virtuality','admin'])->orderBy('name')->get(['id','name'])
            : collect();

        // Para el select de plantillas en el modal
        $plantillas = Plantilla::orderBy('nombre')->get(['id','nombre','descripcion']);

        return view('solicitudes.index', compact('solicitudes','q','usuarios','plantillas'))
               ->with('clienteSel', $cliente);
    }

    /** Crear (modal en index) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'    => ['required','exists:clientes_asignaciones,id'],
            'no_serie'      => ['nullable','string','max:120'],
            'dispositivo'   => ['required','string','max:150'],
            'modelo'        => ['nullable','string','max:150'],
            'plantilla_id'  => ['required','exists:plantillas,id'],
            'tipo_servicio' => ['nullable','string','max:150'],
            'estado'        => ['required','in:pendiente,en_proceso,finalizado'],
            'descripcion'   => ['nullable','string','max:2000'],
        ]);

        // Rellenar nombre/descripcion desde la plantilla elegida
        $p = Plantilla::findOrFail($data['plantilla_id']);
        $data['tipo_servicio'] = $data['tipo_servicio'] ?: $p->nombre;
        if (empty($data['descripcion']) && $p->descripcion) {
            $data['descripcion'] = $p->descripcion;
        }

        Solicitud::create($data);

        return back()->with('ok', 'Solicitud creada.');
    }

    /** Actualizar (modal en index) */
    public function update(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'cliente_id'    => ['required','exists:clientes_asignaciones,id'],
            'no_serie'      => ['nullable','string','max:120'],
            'dispositivo'   => ['required','string','max:150'],
            'modelo'        => ['nullable','string','max:150'],
            'plantilla_id'  => ['required','exists:plantillas,id'],
            'tipo_servicio' => ['nullable','string','max:150'],
            'estado'        => ['required','in:pendiente,en_proceso,finalizado'],
            'descripcion'   => ['nullable','string','max:2000'],
        ]);

        $p = Plantilla::findOrFail($data['plantilla_id']);
        if (empty($data['tipo_servicio'])) {
            $data['tipo_servicio'] = $p->nombre;
        }

        $oldPlantilla = $solicitud->plantilla_id;
        $oldEstado    = $solicitud->estado;

        $solicitud->update($data);

        $plantillaCambio = (int) $oldPlantilla !== (int) $solicitud->plantilla_id;
        $estadoReiniciado = $data['estado'] === Solicitud::PENDIENTE && $oldEstado !== Solicitud::PENDIENTE;

        if ($plantillaCambio || $estadoReiniciado) {
            $solicitud->pasos()->delete();
            $solicitud->syncPasosFromPlantilla();
        }

        return back()->with('ok', 'Solicitud actualizada.');
    }

    /** Borrar */
    public function destroy(Solicitud $solicitud)
    {
        $solicitud->delete();
        return back()->with('ok', 'Solicitud eliminada.');
    }

    /** Asignar a un usuario (solo admin) */
    public function assign(Request $request, Solicitud $solicitud)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
        ]);

        // Permite asignar solo a usuarios con rol virtuality o admin
        $user = User::whereKey($request->integer('user_id'))
            ->role(['virtuality','admin'])
            ->firstOrFail();

        // Campo correcto según tu esquema: asignado_a
        $solicitud->asignado_a = $user->id;
        $solicitud->save();

        return back()->with('ok', 'Solicitud asignada.');
    }
}
