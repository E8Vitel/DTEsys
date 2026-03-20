<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Firma digital simulada (texto o canvas)
 */
class FirmaDte extends Model
{
    use HasFactory;

    // Tabla de firmas simuladas aplicadas a los documentos.
    protected $table = 'firmas_dte';

    // Se almacena el tipo de firma y su contenido.
    protected $fillable = ['documento_dte_id', 'tipo_firma', 'firma_texto', 'firma_canvas', 'user_id'];

    public function documento()
    {
        // Documento sobre el cual se aplico la firma.
        return $this->belongsTo(DocumentoDte::class);
    }

    public function user()
    {
        // Usuario que realizo la firma.
        return $this->belongsTo(User::class);
    }
}
