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
        'asignado_a',          // si renombraste a asignado_id, cambia aquí y en la relación
        'no_serie',
        'dispositivo',
        'modelo',
        'plantilla_id',
        'tipo_servicio',
        'estado',              // pendiente | en_proceso | finalizado
        'descripcion',
        'ticket_pdf_path',
        'whatsapp_ticket_status',
        'whatsapp_ticket_sent_at',
        'whatsapp_ticket_error',
        'fecha_vencimiento',   // nullable
    ];

    protected $casts = [
        'cliente_id'        => 'integer',
        'asignado_a'        => 'integer',
        'plantilla_id'      => 'integer',
        'fecha_vencimiento' => 'date',   // Carbon|null
        'whatsapp_ticket_sent_at' => 'datetime',
    ];

    // === Estados (como están en tu BD) ===
    public const PENDIENTE   = 'pendiente';
    public const EN_PROCESO  = 'en_proceso';
    public const FINALIZADO  = 'finalizado';

    // === Relaciones ===
    public function cliente()
    {
        return $this->belongsTo(\App\Models\ClientesAsignacion::class, 'cliente_id');
    }

    public function asignado()
    {
        return $this->belongsTo(\App\Models\User::class, 'asignado_a'); // o 'asignado_id'
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

    // === Helpers de avance ===
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

    // === Helpers de vencimiento ===
    public function getEstaVencidaAttribute(): bool
    {
        if (!$this->fecha_vencimiento) return false;
        if ($this->estado === self::FINALIZADO) return false;
        return $this->fecha_vencimiento->isBefore(today());
    }

    public function getVenceHoyAttribute(): bool
    {
        if (!$this->fecha_vencimiento) return false;
        if ($this->estado === self::FINALIZADO) return false;
        return $this->fecha_vencimiento->isSameDay(today());
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) return null;
        return today()->diffInDays($this->fecha_vencimiento, false); // negativo si ya venció
    }

    /**
     * True when the WhatsApp ticket message is expired (1h after being sent) or was never sent.
     */
    public function getWhatsappTicketExpiredAttribute(): bool
    {
        if (!$this->whatsapp_ticket_sent_at) {
            return true;
        }

        return $this->whatsapp_ticket_sent_at->lt(now()->subHour());
    }

    /**
     * Indicates if the UI should offer a resend button for the ticket WhatsApp message.
     */
    public function getShouldResendWhatsappTicketAttribute(): bool
    {
        if ($this->estado !== self::FINALIZADO) {
            return false;
        }

        if (!$this->whatsapp_ticket_sent_at) {
            return true;
        }

        if ($this->whatsapp_ticket_status !== 'sent') {
            return true;
        }

        return $this->whatsapp_ticket_expired;
    }

    /**
     * Sincroniza solicitud_pasos con los pasos actuales de la plantilla.
     * Crea los faltantes y elimina los que ya no existan en la plantilla.
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
                'solicitud_id'      => $this->id,
                'plantilla_paso_id' => $pid,
            ]);
        }

        SolicitudPaso::where('solicitud_id', $this->id)
            ->whereNotIn('plantilla_paso_id', $ids)
            ->delete();
    }

    // === Scopes de búsqueda/filtrado ===
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

    // === Scopes de vencimiento (para KPIs/tableros) ===
    public function scopeConVencimiento($q)
    {
        return $q->whereNotNull('fecha_vencimiento');
    }

    public function scopeSinVencimiento($q)
    {
        return $q->whereNull('fecha_vencimiento');
    }

    public function scopeVencidas($q)
    {
        return $q->conVencimiento()
                 ->whereIn('estado', [self::PENDIENTE, self::EN_PROCESO])
                 ->whereDate('fecha_vencimiento', '<', today());
    }

    public function scopeVenceHoy($q)
    {
        return $q->conVencimiento()
                 ->whereIn('estado', [self::PENDIENTE, self::EN_PROCESO])
                 ->whereDate('fecha_vencimiento', today());
    }

    public function scopePorVencer($q, int $dias = 7)
    {
        return $q->conVencimiento()
                 ->whereIn('estado', [self::PENDIENTE, self::EN_PROCESO])
                 ->whereBetween('fecha_vencimiento', [today(), today()->addDays($dias)]);
    }

    public function scopeOrdenVencimiento($q)
    {
        // Primero con fecha, ordenados por más próximo; al final los que no tienen fecha
        return $q->orderByRaw('fecha_vencimiento IS NULL ASC')
                 ->orderBy('fecha_vencimiento');
    }
}
