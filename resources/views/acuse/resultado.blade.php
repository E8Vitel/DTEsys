<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0;padding:24px;background:#ecfdf5;font-family:Arial,sans-serif;color:#111827;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #a7f3d0;border-radius:12px;padding:28px;">
        <h1 style="margin-top:0;">{{ $titulo }}</h1>
        <p>{{ $mensaje }}</p>
        <p><strong>Estado registrado:</strong> {{ $estado }}</p>
    </div>
</body>
</html>
