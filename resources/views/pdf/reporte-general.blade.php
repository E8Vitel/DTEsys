<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte General DTEsys</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .totales { margin-top: 30px; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Reporte General de Documentos Tributarios Electrónicos</h1>
    <p>Generado el: {{ $fecha_generacion }}</p>

    <table>
        <thead>
            <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Fecha Emisión</th>
                <th>Receptor</th>
                <th>Neto</th>
                <th>IVA</th>
                <th>Total</th>
                <th>Estado Actual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dtes as $dte)
                <tr>
                    <td>{{ $dte->folio }}</td>
                    <td>{{ $dte->tipoDte?->nombre ?? 'Sin tipo' }}</td>
                    <td>{{ \Carbon\Carbon::parse($dte->fecha_emision)->format('d/m/Y') }}</td>
                    <td>{{ $dte->razon_social_receptor }} ({{ $dte->rut_receptor }})</td>
                    <td>${{ number_format($dte->monto_neto, 0, ',', '.') }}</td>
                    <td>${{ number_format($dte->iva, 0, ',', '.') }}</td>
                    <td>${{ number_format($dte->monto_total, 0, ',', '.') }}</td>
                    <td>{{ $dte->historial->last()?->estado_nuevo ?? 'Borrador' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p>Total Neto: ${{ number_format($total_neto, 0, ',', '.') }}</p>
        <p>Total IVA: ${{ number_format($total_iva, 0, ',', '.') }}</p>
        <p><strong>Total General: ${{ number_format($total_general, 0, ',', '.') }}</strong></p>
    </div>
</body>
</html>