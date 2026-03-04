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

    protected $table = 'detalles_dte';

    protected $fillable = ['documento_dte_id', 'cantidad', 'descripcion', 'precio_unitario', 'subtotal'];

    public function documento()
    {
        return $this->belongsTo(DocumentoDte::class);
    }
}