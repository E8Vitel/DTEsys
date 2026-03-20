<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ítems de cada DTE
 */
class DetalleDte extends Model
{
    use HasFactory;

    // Tabla de detalle de items por documento.
    protected $table = 'detalles_dte';

    // Datos minimos de cada linea del DTE.
    protected $fillable = ['documento_dte_id', 'cantidad', 'descripcion', 'precio_unitario', 'subtotal'];

    public function documento()
    {
        // Documento al que pertenece esta linea.
        return $this->belongsTo(DocumentoDte::class);
    }
}
