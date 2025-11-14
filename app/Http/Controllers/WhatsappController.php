<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
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
    protected function formateaNumero(string $tel): string
    {
        $digits = preg_replace('/\D+/', '', $tel) ?? '';

        if ($digits === '') {
            return $digits;
        }

        if (! str_starts_with($digits, '52')) {
            $digits = '52' . $digits;
        }

        return $digits;
    }

    /**
     * Send the WhatsApp template message for the provided solicitud.
     */
    public function enviarPorSolicitud(Solicitud $solicitud): JsonResponse
    {
        $solicitud->loadMissing('cliente');

        $cliente = $solicitud->cliente;

        $telefonoCliente = $cliente?->telefono; // Cambia "telefono" si tu columna se llama distinto.
        $nombreCliente = $cliente?->nombre;     // Cambia "nombre" si tu columna se llama distinto.
        $tipoServicio = $solicitud->tipo_servicio; // Ajusta si la columna del tipo de servicio tiene otro nombre.
        $urlTicket = url("/ordenes/{$solicitud->id}/ticket");

        $numeroFormateado = $this->formateaNumero((string) $telefonoCliente);

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
                            ['type' => 'text', 'text' => (string) $nombreCliente],
                            ['type' => 'text', 'text' => (string) $tipoServicio],
                            ['type' => 'text', 'text' => $urlTicket],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken(config('services.whatsapp.token'))
            ->post($this->endpoint(), $payload);

        return response()->json([
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);
    }
}
