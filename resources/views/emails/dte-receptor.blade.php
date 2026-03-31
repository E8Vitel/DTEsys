<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DTE {{ $dte->folio }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;">
        <div style="padding:24px 28px;background:#0f172a;color:#ffffff;">
            <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;">DTEsys</p>
            <h1 style="margin:0;font-size:24px;">Documento {{ $dte->folio }}</h1>
        </div>

        <div style="padding:28px;">
            <p style="margin-top:0;">Se ha enviado un DTE para revisión del receptor.</p>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                <tr>
                    <td style="padding:8px 0;"><strong>Folio</strong></td>
                    <td style="padding:8px 0;">{{ $dte->folio }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Receptor</strong></td>
                    <td style="padding:8px 0;">{{ $dte->razon_social_receptor }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>RUT</strong></td>
                    <td style="padding:8px 0;">{{ $dte->rut_receptor }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;"><strong>Total</strong></td>
                    <td style="padding:8px 0;">${{ number_format((float) $dte->monto_total, 0, ',', '.') }}</td>
                </tr>
            </table>

            <p style="margin-bottom:18px;">Seleccione una acción para registrar la respuesta comercial:</p>

            <div style="margin:24px 0;">
                <a href="{{ $urlsAcuse['aceptado'] }}" style="display:inline-block;margin:0 12px 12px 0;padding:12px 18px;background:#059669;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;">Acusar Recibo</a>
                <a href="{{ $urlsAcuse['reparo'] }}" style="display:inline-block;margin:0 12px 12px 0;padding:12px 18px;background:#d97706;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;">Acusar con Reparos</a>
                <a href="{{ $urlsAcuse['rechazado'] }}" style="display:inline-block;margin:0 12px 12px 0;padding:12px 18px;background:#b91c1c;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;">Rechazar Documento</a>
            </div>

            <p style="margin-bottom:0;color:#6b7280;font-size:14px;">El correo incluye el XML y el PDF del documento como adjuntos.</p>
        </div>
    </div>
</body>
</html>
