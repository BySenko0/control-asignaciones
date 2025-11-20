<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;

class WhatsappController extends Controller
{
    public function __construct(private WhatsappService $whatsapp)
    {
    }

    /**
     * Send the WhatsApp template message for the provided solicitud.
     */
    public function enviarPorSolicitud(Solicitud $solicitud): JsonResponse
    {
        $result = $this->whatsapp->sendTicketTemplateWithTracking($solicitud);

        return response()->json($result);
    }
}