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

    protected $table = 'tipos_dte';

    protected $fillable = ['codigo', 'nombre'];

    public function documentos()
    {
        return $this->hasMany(DocumentoDte::class);
    }
}