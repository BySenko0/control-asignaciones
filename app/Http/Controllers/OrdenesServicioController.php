<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\ClientesAsignacion;
use App\Models\PlantillaPaso;
use App\Models\SolicitudPaso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdenesServicioController extends Controller
{
    /**
     * Listado por estado: pendiente | en_proceso | finalizado
     * Además acepta "vencidas" como filtro especial (fecha_vencimiento < hoy y estado != finalizado)
     */
    public function index(Request $request, string $estado)
    {
        $q         = trim((string) $request->get('q',''));
        $clienteId = $request->integer('cliente_id') ?: null;
        $soloMias  = (bool) $request->boolean('solo_mias');

        $user    = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isVirt  = $user->hasRole('virtuality');

        $qb = Solicitud::with(['cliente:id,nombre_cliente','asignado:id,name','plantilla:id,nombre'])
            ->when($q, fn($qq)=>$qq->where(function($w) use($q){
                $w->where('no_serie','like',"%{$q}%")
                  ->orWhere('dispositivo','like',"%{$q}%")
                  ->orWhere('modelo','like',"%{$q}%")
                  ->orWhere('tipo_servicio','like',"%{$q}%");
            }))
            ->when($clienteId, fn($qq)=>$qq->where('cliente_id',$clienteId))
            ->withCount([
                'pasos as pasos_hechos_count' => fn($q)=>$q->where('hecho',true),
            ])
            ->addSelect([
                'total_pasos' => PlantillaPaso::selectRaw('count(*)')
                    ->whereColumn('plantilla_id','solicitudes.plantilla_id')
            ]);

        // Filtro principal por "estado" o por "vencidas"
        if ($estado === 'vencidas') {
            // No finalizadas, con fecha vencida
            $qb->whereIn('estado', ['pendiente','en_proceso'])
               ->whereNotNull('fecha_vencimiento')
               ->whereDate('fecha_vencimiento','<', today())
               ->orderBy('fecha_vencimiento'); // más urgente arriba
        } else {
            // estados reales en BD
            $qb->where('estado', $estado)
               ->orderByDesc('id');
        }

        // Visibilidad por rol
        if ($isVirt && !$isAdmin) {
            $qb->where('asignado_a', $user->id);
        } elseif ($isAdmin && $soloMias) {
            $qb->where('asignado_a', $user->id);
        }

        $solicitudes = $qb->paginate(12)->withQueryString();
        $clientes    = ClientesAsignacion::orderBy('nombre_cliente')->get(['id','nombre_cliente']);

        // Títulos de pestaña
        $titulo = match ($estado) {
            'pendiente'  => 'Pendientes',
            'en_proceso' => 'En proceso',
            'finalizado' => 'Finalizadas',
            'vencidas'   => 'Vencidas',
            default      => ucfirst($estado),
        };

        return view('ordenes.index', compact(
            'estado','titulo','solicitudes','q','clienteId','clientes','soloMias','isAdmin','isVirt'
        ));
    }

    /**
     * Checklist de una solicitud.
     * - Si estaba pendiente, la pone en en_proceso.
     * - Asegura los registros de solicitud_pasos contra los pasos de la plantilla.
     */
    public function checklist(Solicitud $solicitud)
    {
        $user = Auth::user();
        abort_unless($user->hasRole('admin') || $solicitud->asignado_a === $user->id, 403);

        $puedeGestionar = $solicitud->asignado_a === $user->id;

        if ($puedeGestionar && $solicitud->estado === Solicitud::PENDIENTE) {
            $solicitud->update(['estado' => Solicitud::EN_PROCESO]);
        }

        // Asegurar registros de pasos (ordenados por 'numero')
        $pasosPlantilla = PlantillaPaso::where('plantilla_id', $solicitud->plantilla_id)
            ->orderBy('numero')
            ->get();

        foreach ($pasosPlantilla as $pp) {
            SolicitudPaso::firstOrCreate([
                'solicitud_id'      => $solicitud->id,
                'plantilla_paso_id' => $pp->id,
            ]);
        }

        // Cargar relaciones y conteo
        $solicitud->load([
            'plantilla.pasos' => fn($q) => $q->orderBy('numero'),
            'pasos.paso',
        ])->loadCount([
            'pasos as pasos_hechos_count' => fn($q) => $q->where('hecho', true),
        ]);

        return view('ordenes.checklist', [
            'solicitud'      => $solicitud,
            'puedeGestionar' => $puedeGestionar,
        ]);
    }

    /**
     * Marca / desmarca un paso del checklist.
     */
    public function togglePaso(Request $request, Solicitud $solicitud, PlantillaPaso $paso)
    {
        $user = Auth::user();
        abort_unless($solicitud->asignado_a === $user->id, 403);
        abort_unless($paso->plantilla_id === $solicitud->plantilla_id, 404);

        $sp = SolicitudPaso::firstOrCreate([
            'solicitud_id'      => $solicitud->id,
            'plantilla_paso_id' => $paso->id,
        ]);

        $nuevo = !$sp->hecho;

        $sp->forceFill([
            'hecho'   => $nuevo,
            'done_at' => $nuevo ? now() : null,
            'done_by' => $nuevo ? $user->id : null,
            'notas'   => $request->string('notas')->toString() ?: $sp->notas,
        ])->save();

        $total  = PlantillaPaso::where('plantilla_id', $solicitud->plantilla_id)->count();
        $hechos = SolicitudPaso::where('solicitud_id', $solicitud->id)->where('hecho', true)->count();

        if ($total > 0 && $hechos >= $total) {
            $solicitud->update(['estado' => 'finalizado']);
            return redirect()->route('ordenes.finalizadas') // <— ruta corregida
                             ->with('ok', 'Orden finalizada automáticamente.');
        }

        return back()->with('ok', $nuevo ? 'Paso marcado.' : 'Paso desmarcado.');
    }

    /**
     * Finalizar manualmente (opcional).
     */
    public function finalizar(Solicitud $solicitud)
    {
        $user = Auth::user();
        abort_unless($solicitud->asignado_a === $user->id, 403);

        $total  = PlantillaPaso::where('plantilla_id', $solicitud->plantilla_id)->count();
        $hechos = SolicitudPaso::where('solicitud_id', $solicitud->id)->where('hecho', true)->count();

        if ($total > 0 && $hechos >= $total) {
            $solicitud->update(['estado' => 'finalizado']);
            return redirect()->route('ordenes.finalizadas') // <— ruta corregida
                             ->with('ok', 'Orden finalizada.');
        }

        return back()->with('error', 'Aún faltan pasos por completar.');
    }
}
