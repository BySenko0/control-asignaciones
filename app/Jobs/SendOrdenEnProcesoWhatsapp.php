<?php

namespace App\Jobs;

use App\Models\Solicitud;
use App\Models\User;
use App\Models\WhatsappNotification;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrdenEnProcesoWhatsapp implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $notificationId)
    {
    }

    public function handle(WhatsappService $whatsapp): void
    {
        $notification = WhatsappNotification::find($this->notificationId);

        if (!$notification || $notification->status !== WhatsappNotification::STATUS_PENDING) {
            return;
        }

        $solicitud = Solicitud::with(['cliente', 'asignado'])->find($notification->solicitud_id);

        if (!$solicitud) {
            $notification->forceFill([
                'status' => WhatsappNotification::STATUS_FAILED,
                'error' => 'Solicitud no encontrada.',
                'sent_at' => now(),
            ])->save();

            Log::warning('No se encontró la solicitud para enviar WhatsApp de orden en proceso.', [
                'notification_id' => $notification->id,
                'solicitud_id' => $notification->solicitud_id,
            ]);

            return;
        }

        $cliente = $solicitud->cliente;
        $clienteNombre = $cliente?->nombre_cliente ?: ($cliente?->nombre ?? null);
        $tipoServicio = $solicitud->tipo_servicio;
        $empleadoNombre = $solicitud->asignado?->name;

        if (!$empleadoNombre && $notification->actor_id) {
            $empleadoNombre = User::find($notification->actor_id)?->name;
        }

        $fechaEntrega = $solicitud->fecha_vencimiento?->format('Y-m-d');
        $telefonoNormalizado = $whatsapp->normalizeNumber($cliente?->telefono);

        $faltantes = [];

        if (!$whatsapp->isValidNumber($telefonoNormalizado)) {
            $faltantes[] = 'telefono';
        }

        if (!$clienteNombre) {
            $faltantes[] = 'cliente';
        }

        if (!$tipoServicio) {
            $faltantes[] = 'servicio';
        }

        if (!$empleadoNombre) {
            $faltantes[] = 'empleado';
        }

        if (!$fechaEntrega) {
            $faltantes[] = 'fecha_entrega';
        }

        if ($faltantes !== []) {
            $notification->forceFill([
                'status' => WhatsappNotification::STATUS_SKIPPED,
                'phone' => $telefonoNormalizado ?: null,
                'error' => 'Datos incompletos: ' . implode(', ', $faltantes) . '.',
                'sent_at' => now(),
            ])->save();

            Log::warning('No se envió WhatsApp de orden en proceso por datos incompletos.', [
                'notification_id' => $notification->id,
                'solicitud_id' => $solicitud->id,
                'faltantes' => $faltantes,
            ]);

            return;
        }

        $parameters = [
            (string) $clienteNombre,
            (string) $tipoServicio,
            (string) $empleadoNombre,
            (string) $fechaEntrega,
        ];

        $notification->forceFill([
            'phone' => $telefonoNormalizado,
            'template_name' => 'mensaje',
            'parameters' => $parameters,
        ])->save();

        $result = $whatsapp->sendOrdenEnProcesoTemplate($telefonoNormalizado, $parameters);
        $ok = $result['ok'] ?? false;

        $responseBody = $this->stringifyBody($result['body'] ?? null);

        $notification->forceFill([
            'status' => $ok ? WhatsappNotification::STATUS_SENT : WhatsappNotification::STATUS_FAILED,
            'response' => $responseBody,
            'error' => $ok ? null : $responseBody,
            'sent_at' => now(),
        ])->save();

        if (!$ok) {
            Log::warning('Fallo al enviar WhatsApp de orden en proceso.', [
                'notification_id' => $notification->id,
                'solicitud_id' => $solicitud->id,
                'response' => $result['body'] ?? null,
            ]);
        }
    }

    private function stringifyBody(mixed $body): ?string
    {
        if (is_null($body)) {
            return null;
        }

        if (is_string($body)) {
            return $body;
        }

        $encoded = json_encode($body, JSON_UNESCAPED_UNICODE);

        return $encoded === false ? null : $encoded;
    }
}
