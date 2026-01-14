<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    public const EVENT_ORDEN_EN_PROCESO = 'orden_en_proceso';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'solicitud_id',
        'actor_id',
        'event_type',
        'target_state',
        'phone',
        'template_name',
        'parameters',
        'status',
        'response',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'sent_at' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
