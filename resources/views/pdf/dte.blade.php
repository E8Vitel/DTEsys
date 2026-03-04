<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>DTE {{ $dte->folio }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DTE Simulado - {{ $dte->tipo_dte->nombre }}</h1>
        <p>Folio: {{ $dte->folio }} | Emisión: {{ \Carbon\Carbon::parse($dte->fecha_emision)->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <strong>Emisor:</strong><br>
        {{ $dte->razon_social_emisor }}<br>
        RUT: {{ $dte->rut_emisor }}
    </div>

    <div class="section">
        <strong>Receptor:</strong><br>
        {{ $dte->razon_social_receptor }}<br>
        RUT: {{ $dte->rut_receptor }}
    </div>

    <div class="section">
        <h3>Detalles</h3>
        <table>
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Descripción</th>
                    <th>P. Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dte->detalles as $det)
                    <tr>
                        <td>{{ $det->cantidad }}</td>
                        <td>{{ $det->descripcion }}</td>
                        <td>${{ number_format($det->precio_unitario, 0) }}</td>
                        <td>${{ number_format($det->subtotal, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section text-right">
        <p><strong>Neto:</strong> ${{ number_format($dte->monto_neto, 0) }}</p>
        <p><strong>IVA:</strong> ${{ number_format($dte->iva, 0) }}</p>
        <p><strong>Total:</strong> ${{ number_format($dte->monto_total, 0) }}</p>
    </div>

    @if($dte->firma)
        <div class="section">
            <strong>Firma:</strong><br>
            @if($dte->firma->tipo_firma == 'texto')
                {{ $dte->firma->firma_texto }}
            @else
                <img src="{{ $dte->firma->firma_canvas }}" style="max-height: 100px;">
            @endif
        </div>
    @endif

    <div class="section text-center">
        <p>Timbre simulado: {{ $dte->timbre }}</p>
    </div>
</body>
</html>