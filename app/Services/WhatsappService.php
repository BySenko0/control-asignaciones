<?php

namespace App\Services;

use App\Jobs\SendOrdenEnProcesoWhatsapp;
use App\Models\Solicitud;
use App\Models\User;
use App\Models\WhatsappNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Build the WhatsApp Cloud API endpoint URL.
     */
    protected function endpoint(): string
    {
        $version = config('services.whatsapp.version');
        $phoneId = config('services.whatsapp.phone_id');

        return sprintf('https://graph.facebook.com/%s/%s/messages', $version, $phoneId);
    }

    /**
     * Normalize a phone number so it is ready for WhatsApp (digits only, prefixed with Mexico's country code 52).
     */
    protected function formatNumber(string $tel): string
    {
        $digits = preg_replace('/\D+/', '', $tel) ?? '';

        if ($digits === '') {
            return $digits;
        }

        if (!str_starts_with($digits, '52')) {
            $digits = '52' . $digits;
        }

        return $digits;
    }

    public function normalizeNumber(?string $tel): string
    {
        return $this->formatNumber((string) $tel);
    }

    public function isValidNumber(string $tel): bool
    {
        return $tel !== '' && str_starts_with($tel, '52') && ctype_digit($tel) && strlen($tel) >= 12;
    }

    protected function successfulStatus(?int $status): bool
    {
        return !is_null($status) && $status >= 200 && $status < 300;
    }

    protected function stringifyBody(mixed $body): string
    {
        if (is_string($body)) {
            return $body;
        }

        $encoded = json_encode($body, JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '' : $encoded;
    }

    /**
     * Send WhatsApp template for the given solicitud and return response details.
     */
    public function sendTicketTemplate(Solicitud $solicitud): array
    {
        $solicitud->loadMissing('cliente');

        $cliente = $solicitud->cliente;

        $telefonoCliente = $cliente?->telefono; // Cambia "telefono" si tu columna se llama distinto.
        $nombreCliente = $cliente?->nombre;     // Cambia "nombre" si tu columna se llama distinto.
        $tipoServicio = $solicitud->tipo_servicio; // Ajusta si la columna del tipo de servicio tiene otro nombre.

        $nombreCliente = $nombreCliente ?: 'Cliente';
        $tipoServicio = $tipoServicio ?: 'Servicio';

        $numeroFormateado = $this->formatNumber((string) $telefonoCliente);

        if ($numeroFormateado === '') {
            Log::warning('No se envió WhatsApp: número vacío.', [
                'solicitud_id' => $solicitud->id,
            ]);

            return [
                'status' => null,
                'body' => ['error' => 'Número de teléfono no disponible.'],
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $numeroFormateado,
            'type' => 'template',
            'template' => [
                'name' => 'ticket_de_servicio_prueba',
                'language' => ['code' => 'es'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => (string) $nombreCliente], // {{1}}
                            ['type' => 'text', 'text' => (string) $tipoServicio], // {{2}}
                        ],
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => '0',
                        'parameters' => [
                            ['type' => 'text', 'text' => (string) $solicitud->id], // {{1}} dynamic part of the URL
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken(config('services.whatsapp.token'))
            ->post($this->endpoint(), $payload);

        $body = $response->json();
        if (is_null($body)) {
            $body = $response->body();
        }

        return [
            'status' => $response->status(),
            'body' => $body,
        ];
    }

    /**
     * Send WhatsApp template for orden en proceso notification.
     */
    public function sendOrdenEnProcesoTemplate(string $telefono, array $parameters): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $telefono,
            'type' => 'template',
            'template' => [
                'name' => 'mensaje',
                'language' => ['code' => 'en'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => (string) ($parameters[0] ?? '')],
                            ['type' => 'text', 'text' => (string) ($parameters[1] ?? '')],
                            ['type' => 'text', 'text' => (string) ($parameters[2] ?? '')],
                            ['type' => 'text', 'text' => (string) ($parameters[3] ?? '')],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken(config('services.whatsapp.token'))
            ->post($this->endpoint(), $payload);

        $body = $response->json();
        if (is_null($body)) {
            $body = $response->body();
        }

        return [
            'status' => $response->status(),
            'body' => $body,
            'ok' => $this->successfulStatus($response->status()),
        ];
    }

    /**
     * Queue WhatsApp notification for orden en proceso state.
     */
    public function queueOrdenEnProcesoNotification(Solicitud $solicitud, ?User $actor = null): ?WhatsappNotification
    {
        $notification = WhatsappNotification::firstOrCreate(
            [
                'solicitud_id' => $solicitud->id,
                'event_type' => WhatsappNotification::EVENT_ORDEN_EN_PROCESO,
                'target_state' => Solicitud::EN_PROCESO,
            ],
            [
                'actor_id' => $actor?->id,
                'template_name' => 'mensaje',
                'status' => WhatsappNotification::STATUS_PENDING,
            ]
        );

        if (!$notification->wasRecentlyCreated) {
            Log::info('WhatsApp orden en proceso ya registrado; se omite el envío duplicado.', [
                'notification_id' => $notification->id,
                'solicitud_id' => $solicitud->id,
            ]);

            return null;
        }

        SendOrdenEnProcesoWhatsapp::dispatch($notification->id);

        return $notification;
    }

    /**
     * Send WhatsApp template and persist the sending result in the solicitud record.
     */
    public function sendTicketTemplateWithTracking(Solicitud $solicitud): array
    {
        try {
            $result = $this->sendTicketTemplate($solicitud);
            $ok = $this->successfulStatus($result['status']);

            $solicitud->forceFill([
                'whatsapp_ticket_status' => $ok ? 'sent' : 'failed',
                'whatsapp_ticket_sent_at' => now(),
                'whatsapp_ticket_error' => $ok ? null : $this->stringifyBody($result['body']),
            ])->save();

            if (!$ok) {
                Log::warning('Fallo al enviar mensaje de WhatsApp para la solicitud.', [
                    'solicitud_id' => $solicitud->id,
                    'response' => $result['body'],
                ]);
            }

            return $result + ['ok' => $ok];
        } catch (\Throwable $e) {
            $solicitud->forceFill([
                'whatsapp_ticket_status' => 'error',
                'whatsapp_ticket_sent_at' => now(),
                'whatsapp_ticket_error' => $e->getMessage(),
            ])->save();

            Log::error('Error inesperado al enviar mensaje de WhatsApp para la solicitud.', [
                'solicitud_id' => $solicitud->id,
                'exception' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => null,
                'body' => $e->getMessage(),
            ];
        }
    }
}
