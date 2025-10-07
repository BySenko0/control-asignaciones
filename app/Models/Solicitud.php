<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'cliente_id',
        'no_serie',
        'dispositivo',
        'modelo',
        'tipo_servicio',
        'estado',        // pendiente | en_proceso | finalizado
        'descripcion',
        'asignado_a',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'asignado_a' => 'integer',
    ];

    // Estados (constantes)
    public const PENDIENTE   = 'pendiente';
    public const EN_PROCESO  = 'en_proceso';
    public const FINALIZADO  = 'finalizado';

    // Relaciones
    public function cliente()
    {
        // FK cliente_id -> clientes_asignaciones.id
        return $this->belongsTo(\App\Models\ClientesAsignacion::class, 'cliente_id');
    }

    public function asignado()
    {
        // FK asignado_a -> users.id
        return $this->belongsTo(\App\Models\User::class, 'asignado_a');
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
              ->orWhere('estado', 'like', "%{$term}%");
        });
    }

    public function scopeEstado($q, ?string $estado)
    {
        return $estado ? $q->where('estado', $estado) : $q;
    }
}
