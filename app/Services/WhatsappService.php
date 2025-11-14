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
        $urlTicket = url("/ordenes/{$solicitud->id}/ticket");

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
                            ['type' => 'text', 'text' => $urlTicket],             // {{3}}
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
}
