<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Solicitud;
use App\Models\ClientesAsignacion; // <- IMPORTANTE

class SolicitudesClienteController extends Controller
{
    public function index(Request $request, ?ClientesAsignacion $cliente = null) // <- tip correcto
    {
        $q = trim((string) $request->get('q', ''));
        $clienteId = $cliente?->id;

        $solicitudes = Solicitud::with(['cliente','asignado'])
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

        $usuarios = Auth::user()->hasRole('admin')
            ? User::orderBy('name')->get(['id','name'])
            : collect();

        return view('solicitudes.index', compact('solicitudes','q','usuarios'))
               ->with('clienteSel', $cliente);
    }

    /** Crear (modal en index) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'    => ['required','exists:clientes,id'],
            'no_serie'      => ['nullable','string','max:100'],
            'dispositivo'   => ['required','string','max:150'],
            'modelo'        => ['nullable','string','max:150'],
            'tipo_servicio' => ['required','string','max:150'],
            'estado'        => ['required','in:pendiente,en_proceso,resuelta'],
            'descripcion'   => ['nullable','string','max:2000'],
        ]);

        Solicitud::create($data);

        return back()->with('ok', 'Solicitud creada.');
    }

    /** Actualizar (modal en index) */
    public function update(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'cliente_id'    => ['required','exists:clientes,id'],
            'no_serie'      => ['nullable','string','max:100'],
            'dispositivo'   => ['required','string','max:150'],
            'modelo'        => ['nullable','string','max:150'],
            'tipo_servicio' => ['required','string','max:150'],
            'estado'        => ['required','in:pendiente,en_proceso,resuelta'],
            'descripcion'   => ['nullable','string','max:2000'],
        ]);

        $solicitud->update($data);

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

        $data = $request->validate([
            'user_id' => ['required','exists:users,id'],
        ]);

        $solicitud->asignado_a = $data['user_id'];
        $solicitud->save();

        return back()->with('ok', 'Solicitud asignada.');
    }
}
