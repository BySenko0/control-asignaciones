<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $hoy = today();

        $baseQuery = Solicitud::query();

        // Usuarios no administradores solo ven sus asignaciones
        if (!$isAdmin) {
            $baseQuery->where('asignado_a', $user->id);
        }

        $kpis = [
            'pendientes'   => (clone $baseQuery)->where('estado','pendiente')->count(),
            'en_proceso'   => (clone $baseQuery)->where('estado','en_proceso')->count(),
            'finalizadas'  => (clone $baseQuery)->where('estado','finalizado')->count(),

            // Vencidas: con fecha, no finalizadas y fecha < hoy
            'vencidas'     => (clone $baseQuery)->whereIn('estado',['pendiente','en_proceso'])
                                ->whereNotNull('fecha_vencimiento')
                                ->whereDate('fecha_vencimiento','<',$hoy)
                                ->count(),

            // Vencen hoy: con fecha, no finalizadas y = hoy
            'vencen_hoy'   => (clone $baseQuery)->whereIn('estado',['pendiente','en_proceso'])
                                ->whereNotNull('fecha_vencimiento')
                                ->whereDate('fecha_vencimiento',$hoy)
                                ->count(),

            // No asignadas: abiertas sin asignado
            'no_asignadas' => (clone $baseQuery)->whereIn('estado',['pendiente','en_proceso'])
                                ->whereNull('asignado_a')
                                ->count(),
        ];

        return view('dashboard', compact('kpis'));
    }
}