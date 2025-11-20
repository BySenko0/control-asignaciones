<?php

namespace App\Services;

use App\Models\Solicitud;
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
