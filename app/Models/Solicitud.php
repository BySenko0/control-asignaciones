<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SolicitudPaso;
use App\Models\PlantillaPaso;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'cliente_id',
        'asignado_a',     // ← usa este nombre en controladores si tu columna se llama así
        'no_serie',
        'dispositivo',
        'modelo',
        'plantilla_id',
        'tipo_servicio',
        'estado',         // pendiente | en_proceso | finalizado
        'descripcion',
    ];

    protected $casts = [
        'cliente_id'   => 'integer',
        'asignado_a'   => 'integer',
        'plantilla_id' => 'integer',
    ];

    // Estados
    public const PENDIENTE   = 'pendiente';
    public const EN_PROCESO  = 'en_proceso';
    public const FINALIZADO  = 'finalizado';

    // Relaciones base
    public function cliente()
    {
        return $this->belongsTo(\App\Models\ClientesAsignacion::class, 'cliente_id');
    }

    public function asignado()
    {
        return $this->belongsTo(\App\Models\User::class, 'asignado_a'); // si cambias a asignado_id, ajústalo aquí
    }

    public function plantilla()
    {
        return $this->belongsTo(\App\Models\Plantilla::class, 'plantilla_id');
    }

    // === Checklist ===
    public function pasos()
    {
        return $this->hasMany(SolicitudPaso::class);
    }

    public function pasosHechos()
    {
        return $this->pasos()->where('hecho', true);
    }

    // Helpers de avance (opcionales, útiles en Blade)
    public function getTotalPasosAttribute(): int
    {
        return $this->plantilla ? (int) $this->plantilla->pasos()->count() : 0;
    }

    public function getAvancePctAttribute(): int
    {
        $total = $this->total_pasos;
        if (!$total) return 0;
        $hechos = (int) $this->pasos()->where('hecho', true)->count();
        return (int) floor(($hechos / $total) * 100);
    }

    /**
     * Sincroniza solicitud_pasos con los pasos actuales de la plantilla.
     * Crea los faltantes y elimina los que ya no existen en la plantilla.
     */
    public function syncPasosFromPlantilla(): void
    {
        if (!$this->plantilla_id) return;

        $ids = PlantillaPaso::where('plantilla_id', $this->plantilla_id)
            ->orderBy('numero')
            ->pluck('id');

        if ($ids->isEmpty()) {
            SolicitudPaso::where('solicitud_id', $this->id)->delete();
            return;
        }

        foreach ($ids as $pid) {
            SolicitudPaso::firstOrCreate([
                'solicitud_id'       => $this->id,
                'plantilla_paso_id'  => $pid,
            ]);
        }

        SolicitudPaso::where('solicitud_id', $this->id)
            ->whereNotIn('plantilla_paso_id', $ids)
            ->delete();
    }

    // Scopes útiles
    public function scopeDelCliente($q, $clienteId)
    {
        return $clienteId ? $q->where('cliente_id', $clienteId) : $q;
    }

    public function scopeBuscar($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('no_serie', 'like', "%{$term}%")
              ->orWhere('dispositivo', 'like', "%{$term}%")
              ->orWhere('modelo', 'like', "%{$term}%")
              ->orWhere('tipo_servicio', 'like', "%{$term}%")
              ->orWhere('estado', 'like', "%{$term}%")
              ->orWhereHas('plantilla', fn($qq) =>
                    $qq->where('nombre','like',"%{$term}%"));
        });
    }

    public function scopeEstado($q, ?string $estado)
    {
        return $estado ? $q->where('estado', $estado) : $q;
    }

    public function scopeAsignadoA($q, $userId)
    {
        return $q->where('asignado_a', $userId); // cambia a 'asignado_id' si renombras la columna
    }
}
