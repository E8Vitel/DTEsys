<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $dte->folio }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        .title { text-align: center; margin-bottom: 10px; }
        .box { border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .label { color: #6b7280; font-size: 11px; }
        .value { font-weight: 600; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .totals { margin-top: 10px; }
        .no-border td { border: none; padding: 0; }
    </style>
</head>
<body>
    <div class="title">
        <h2 style="margin: 0;">DTE {{ $dte->tipoDte?->codigo ?? 33 }} - {{ $dte->tipoDte?->nombre ?? 'Documento Tributario Electr&oacute;nico' }}</h2>
        <div>Folio: {{ $dte->folio }} | Fecha Emisi&oacute;n: {{ \Carbon\Carbon::parse($dte->fecha_emision)->format('d/m/Y') }}</div>
    </div>

    <div class="box">
        <table class="no-border">
            <tr>
                <td style="width: 50%; padding-right: 8px;">
                    <div class="label">Emisor</div>
                    <div class="value">{{ $dte->razon_social_emisor }}</div>
                    <div>RUT: {{ $dte->rut_emisor }}</div>
                    <div>Direcci&oacute;n: {{ $dte->user?->direccion ?? 'No informada' }}</div>
                </td>
                <td style="width: 50%; padding-left: 8px;">
                    <div class="label">Receptor</div>
                    <div class="value">{{ $dte->razon_social_receptor }}</div>
                    <div>RUT: {{ $dte->rut_receptor }}</div>
                    <div>Giro: {{ $dte->giro_receptor ?? 'No informado' }}</div>
                    <div>Direcci&oacute;n: {{ $dte->direccion_receptor ?? 'No informada' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table>
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Descripci&oacute;n</th>
                    <th class="text-right">P. Unit.</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dte->detalles as $det)
                    <tr>
                        <td>{{ $det->cantidad }}</td>
                        <td>{{ $det->descripcion }}</td>
                        <td class="text-right">${{ number_format($det->precio_unitario, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format($det->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="text-right"><strong>Neto:</strong> ${{ number_format($dte->monto_neto, 0, ',', '.') }}</div>
            <div class="text-right"><strong>IVA (19%):</strong> ${{ number_format($dte->iva, 0, ',', '.') }}</div>
            <div class="text-right"><strong>Total:</strong> ${{ number_format($dte->monto_total, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($dte->firma || $dte->timbre)
        <div class="box">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding-right: 8px;"><div class="label">Firma electr&oacute;nica simulada</div></td>
                    <td style="width: 50%; padding-left: 8px; text-align: center;"><div class="label">Timbre electr&oacute;nico</div></td>
                </tr>
                <tr>
                    <td style="width: 50%; padding-right: 8px; padding-top: 4px;">
                        Firmado por: {{ $dte->firma?->user?->name ?? 'Sin firma' }}
                    </td>
                    <td style="width: 50%; padding-left: 8px; padding-top: 4px; text-align: center;">
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%; padding-right: 8px; padding-top: 8px; vertical-align: top;">
                        @if($dte->firma?->firma_canvas)
                            <img src="{{ $dte->firma->firma_canvas }}" style="max-height: 100px; border: 1px solid #e5e7eb; padding: 4px; background: #fff;">
                        @elseif($dte->firma?->firma_texto)
                            <div style="font-style: italic;">{{ $dte->firma->firma_texto }}</div>
                        @endif
                    </td>
                    <td style="width: 50%; padding-left: 8px; padding-top: 8px; vertical-align: top; text-align: center;">
                        @if(is_string($dte->timbre) && str_starts_with($dte->timbre, 'data:image'))
                            <img src="{{ $dte->timbre }}" style="max-width: 280px; max-height: 120px; border: 1px solid #e5e7eb; padding: 4px; background: #fff;">
                            <div style="margin-top: 4px; font-size: 11px;">Timbre Electr&oacute;nico SII (Simulado)</div>
                        @else
                            <div>Timbre simulado: {{ $dte->timbre }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>