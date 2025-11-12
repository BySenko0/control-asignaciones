<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Solicitud;
use App\Models\ClientesAsignacion;
use App\Models\Plantilla;
use App\Services\TicketPdfGenerator;

class SolicitudesClienteController extends Controller
{ 
    public function __construct(private TicketPdfGenerator $ticketPdfGenerator)
    {
    }
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
            'cliente_id'         => ['required','exists:clientes_asignaciones,id'],
            'no_serie'           => ['nullable','string','max:120'],
            'dispositivo'        => ['required','string','max:150'],
            'modelo'             => ['nullable','string','max:150'],
            'plantilla_id'       => ['required','exists:plantillas,id'],
            'tipo_servicio'      => ['nullable','string','max:150'],
            'estado'             => ['required','in:pendiente,en_proceso,finalizado'],
            'descripcion'        => ['nullable','string','max:2000'],
            'fecha_vencimiento'  => ['nullable','date'], // <- NUEVO
            // Si quieres evitar pasado: ['nullable','date','after_or_equal:today']
        ]);

        
        // Normalizar fecha_vencimiento: si no viene en el request, dejarla en null
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?? null;


        // Rellenar nombre/descripcion desde la plantilla elegida
        $p = Plantilla::findOrFail($data['plantilla_id']);
        $data['tipo_servicio'] = $data['tipo_servicio'] ?: $p->nombre;
        if (empty($data['descripcion']) && $p->descripcion) {
            $data['descripcion'] = $p->descripcion;
        }

        $solicitud = Solicitud::create($data);

        $this->ensureTicketIfFinal($solicitud, true);

        return back()->with('ok', 'Solicitud creada.');
    }

    /** Actualizar (modal en index) */
    public function update(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'cliente_id'         => ['required','exists:clientes_asignaciones,id'],
            'no_serie'           => ['nullable','string','max:120'],
            'dispositivo'        => ['required','string','max:150'],
            'modelo'             => ['nullable','string','max:150'],
            'plantilla_id'       => ['required','exists:plantillas,id'],
            'tipo_servicio'      => ['nullable','string','max:150'],
            'estado'             => ['required','in:pendiente,en_proceso,finalizado'],
            'descripcion'        => ['nullable','string','max:2000'],
            'fecha_vencimiento'  => ['nullable','date'], // <- NUEVO
        ]);

        // Normalizar fecha_vencimiento: "" -> null
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?: null;

        $p = Plantilla::findOrFail($data['plantilla_id']);
        if (empty($data['tipo_servicio'])) {
            $data['tipo_servicio'] = $p->nombre;
        }

        $oldPlantilla = $solicitud->plantilla_id;
        $oldEstado    = $solicitud->estado;

        // Si cambia la plantilla, forzar estado "pendiente"
        $plantillaCambio = (int) $oldPlantilla !== (int) $data['plantilla_id'];
        if ($plantillaCambio) {
            $data['estado'] = Solicitud::PENDIENTE; // asegúrate de tener la constante en el modelo
        }

        $solicitud->update($data);

        $plantillaCambio   = (int) $oldPlantilla !== (int) $solicitud->plantilla_id;
        $estadoReiniciado  = $data['estado'] === Solicitud::PENDIENTE && $oldEstado !== Solicitud::PENDIENTE;

        if ($plantillaCambio || $estadoReiniciado) {
            // Reiniciar pasos del checklist
            $solicitud->pasos()->delete();
            $solicitud->syncPasosFromPlantilla();
        }

        $mensaje = 'Solicitud actualizada.';
        if ($plantillaCambio) {
            $mensaje .= ' Estado reiniciado a pendiente por cambio de plantilla.';
        }

        if ($solicitud->estado === Solicitud::FINALIZADO) {
            $this->ensureTicketIfFinal($solicitud, $oldEstado !== Solicitud::FINALIZADO);
        }

        return back()->with('ok', $mensaje);
    }

    /** Borrar */
    public function destroy(Solicitud $solicitud)
    {
        $solicitud->delete();
        return back()->with('ok', 'Solicitud eliminada.');
    }

    /**
     * Asignar una solicitud.
     * - Admins pueden escoger cualquier usuario con rol permitido.
     * - Usuarios virtuality solo pueden tomarse una solicitud sin asignar.
     */
    public function assign(Request $request, Solicitud $solicitud)
    {
        $actor = Auth::user();

        $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
        ]);

        $userId = (int) $request->integer('user_id');

        if ($actor->hasRole('admin')) {
            $user = User::whereKey($userId)
                ->role(['virtuality','admin'])
                ->firstOrFail();

            $solicitud->asignado_a = $user->id;
            $solicitud->save();

            return back()->with('ok', 'Solicitud asignada.');
        }

        // Virtuality puede "tomar" la solicitud solo para sí mismo,
        // siempre que no esté ya asignada a otro.
        abort_unless($actor->hasRole('virtuality'), 403);
        abort_if($solicitud->asignado_a && $solicitud->asignado_a !== $actor->id, 403);
        abort_unless($userId === $actor->id, 403);

        $solicitud->asignado_a = $actor->id;
        $solicitud->save();

        return back()->with('ok', 'Solicitud tomada.');
}

    private function ensureTicketIfFinal(Solicitud $solicitud, bool $force = false): void
    {
        if ($solicitud->estado !== Solicitud::FINALIZADO) {
            return;
        }

        if (!$force && $solicitud->ticket_pdf_path && Storage::disk('local')->exists($solicitud->ticket_pdf_path)) {
            return;
        }

        $path = $this->ticketPdfGenerator->generate($solicitud);
        $solicitud->forceFill(['ticket_pdf_path' => $path])->save();
    }
}
