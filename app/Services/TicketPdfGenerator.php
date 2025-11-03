<?php

namespace App\Services;

use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class TicketPdfGenerator
{
    public function generate(Solicitud $solicitud): string
    {
        $solicitud->loadMissing([
            'cliente',
            'asignado',
            'plantilla',
            'pasos.paso',
        ]);

        $existing = $solicitud->ticket_pdf_path;
        if ($existing && Storage::disk('local')->exists($existing)) {
            Storage::disk('local')->delete($existing);
        }

        [$primaryColor, $secondaryColor] = $this->paletteForTemplate($solicitud->plantilla?->id);

        $pdf = Pdf::loadView('ordenes.ticket', [
            'solicitud'      => $solicitud,
            'primaryColor'   => $primaryColor,
            'secondaryColor' => $secondaryColor,
            'generatedAt'    => now(),
        ])->setPaper('letter', 'portrait');

        $path = sprintf('tickets/solicitud-%d-%s.pdf',
            $solicitud->id,
            now()->format('YmdHis')
        );

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    private function paletteForTemplate(?int $templateId): array
    {
        if (!$templateId) {
            return ['#1f2937', '#4b5563'];
        }

        $hash = crc32((string) $templateId);
        $hue = $hash % 360;

        $primary = $this->hslToHex($hue, 65, 38);
        $secondary = $this->hslToHex(($hue + 25) % 360, 55, 52);

        return [$primary, $secondary];
    }

    private function hslToHex(int $h, int $s, int $l): string
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hueToRgb($p, $q, $h + 1 / 3);
            $g = $this->hueToRgb($p, $q, $h);
            $b = $this->hueToRgb($p, $q, $h - 1 / 3);
        }

        return sprintf('#%02x%02x%02x',
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }
}
