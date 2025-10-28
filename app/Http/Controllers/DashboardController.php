<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $hoy = today();

        $kpis = [
            'pendientes'   => Solicitud::where('estado','pendiente')->count(),
            'en_proceso'   => Solicitud::where('estado','en_proceso')->count(),
            'finalizadas'  => Solicitud::where('estado','finalizado')->count(),

            // Vencidas: con fecha, no finalizadas y fecha < hoy
            'vencidas'     => Solicitud::whereIn('estado',['pendiente','en_proceso'])
                                ->whereNotNull('fecha_vencimiento')
                                ->whereDate('fecha_vencimiento','<',$hoy)
                                ->count(),

            // Vencen hoy: con fecha, no finalizadas y = hoy
            'vencen_hoy'   => Solicitud::whereIn('estado',['pendiente','en_proceso'])
                                ->whereNotNull('fecha_vencimiento')
                                ->whereDate('fecha_vencimiento',$hoy)
                                ->count(),

            // No asignadas: abiertas sin asignado
            'no_asignadas' => Solicitud::whereIn('estado',['pendiente','en_proceso'])
                                ->whereNull('asignado_a')
                                ->count(),
        ];

        return view('dashboard', compact('kpis'));
    }
}