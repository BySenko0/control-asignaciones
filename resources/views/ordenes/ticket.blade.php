<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket orden #{{ $solicitud->id }}</title>
    <style>
        * { box-sizing: border-box; font-family: 'DejaVu Sans', sans-serif; }
        body { margin: 0; padding: 24px; background: #f7f7f7; color: #1f2933; }
        .card { background: #ffffff; border-radius: 16px; padding: 28px; box-shadow: 0 8px 24px rgba(31,41,55,0.12); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .badge { padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; }
        h1 { margin: 0; font-size: 26px; color: {{ $primaryColor }}; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
        .meta .item { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 16px; background: rgba(148,163,184,0.08); }
        .meta .label { font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.08em; margin-bottom: 4px; }
        .meta .value { font-size: 15px; color: #111827; font-weight: 600; }
        .section-title { font-size: 15px; font-weight: 700; color: {{ $primaryColor }}; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.08em; }
        .steps { border: 1px solid rgba(148,163,184,0.35); border-radius: 14px; padding: 16px; }
        .steps table { width: 100%; border-collapse: collapse; }
        .steps th, .steps td { text-align: left; padding: 10px; font-size: 13px; }
        .steps tr:nth-child(odd) { background: rgba(241,245,249,0.6); }
        .footer { margin-top: 28px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 12px; color: #64748b; }
        .stamp { display: inline-flex; flex-direction: column; align-items: flex-start; border: 2px dashed {{ $secondaryColor }}; padding: 10px 14px; border-radius: 12px; }
        .stamp strong { color: {{ $secondaryColor }}; font-size: 14px; text-transform: uppercase; letter-spacing: 0.06em; }
        .tagline { margin-top: 6px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div>
                <h1>Orden de servicio #{{ $solicitud->id }}</h1>
                <div class="tagline">Plantilla: {{ $solicitud->plantilla->nombre ?? 'No definida' }}</div>
            </div>
            <div class="badge" style="background: {{ $primaryColor }};">Finalizado</div>
        </div>

        <div class="meta">
            <div class="item">
                <div class="label">Cliente</div>
                <div class="value">{{ $solicitud->cliente->nombre_cliente ?? '—' }}</div>
            </div>
            <br>
            <div class="item">
                <div class="label">Asignado a</div>
                <div class="value">{{ $solicitud->asignado->name ?? '—' }}</div>
            </div>
            <br>
            <div class="item">
                <div class="label">No. de serie</div>
                <div class="value">{{ $solicitud->no_serie ?? '—' }}</div>
            </div>
            <br>
            <div class="item">
                <div class="label">Dispositivo</div>
                <div class="value">{{ trim($solicitud->dispositivo.' '.$solicitud->modelo) }}</div>
            </div>
            <br>
            <div class="item">
                <div class="label">Tipo de servicio</div>
                <div class="value">{{ $solicitud->tipo_servicio }}</div>
            </div>
            <br>
            <div class="item">
                <div class="label">Cliente teléfono</div>
                <div class="value">{{ $solicitud->cliente->telefono ?? '—' }}</div>
                <br>
            </div>
        </div>

        <div class="section-title">Servicio realizado</div>
        <div class="service">
            <p>
                {!! nl2br(e($solicitud->descripcion ?? 'No se registró la descripción del servicio.')) !!}
            </p>
        </div>

        <div class="footer">
            <div>
                <div>Generado el {{ $generatedAt->format('d/m/Y H:i') }}</div>
                <div>Documento generado automáticamente al finalizar el checklist.</div>
            </div>
            <div class="stamp">
                <strong>Servicio completado</strong>
                <span>Responsable: {{ $solicitud->asignado->name ?? '—' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
