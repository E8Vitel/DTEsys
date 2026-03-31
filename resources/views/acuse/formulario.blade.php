<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0;padding:24px;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #d1d5db;border-radius:12px;padding:28px;">
        <h1 style="margin-top:0;">{{ $titulo }}</h1>
        <p>Documento: <strong>{{ $dte->folio }}</strong></p>
        <p>Receptor: <strong>{{ $dte->razon_social_receptor }}</strong></p>

        <form method="POST" action="{{ $accion }}">
            @csrf

            <label for="comentario" style="display:block;margin:18px 0 8px;font-weight:700;">Comentario</label>
            <textarea
                id="comentario"
                name="comentario"
                rows="6"
                required
                style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;"
                placeholder="Indique el motivo"
            >{{ old('comentario') }}</textarea>

            @error('comentario')
                <p style="color:#b91c1c;margin-top:8px;">{{ $message }}</p>
            @enderror

            @error('respuesta')
                <p style="color:#b91c1c;margin-top:8px;">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                style="margin-top:18px;padding:12px 18px;border:0;border-radius:8px;background:#0f172a;color:#ffffff;font-weight:700;cursor:pointer;"
            >
                Registrar respuesta
            </button>
        </form>
    </div>
</body>
</html>
