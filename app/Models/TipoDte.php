<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo TipoDTE - códigos oficiales SII
 */
class TipoDte extends Model
{
    use HasFactory;

    // Catalogo de tipos oficiales de DTE.
    protected $table = 'tipos_dte';

    // Codigo tributario y nombre legible.
    protected $fillable = ['codigo', 'nombre'];

    public function documentos()
    {
        // Documentos que usan este tipo.
        return $this->hasMany(DocumentoDte::class);
    }
}
