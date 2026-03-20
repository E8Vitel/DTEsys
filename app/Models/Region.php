<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Catalogo territorial de regiones.
class Region extends Model
{
    use HasFactory;

    // Tabla donde se almacenan las regiones.
    protected $table = 'regiones';

    protected $fillable = ['codigo', 'nombre'];

    public function comunas()
    {
        // Comunas asociadas a esta region.
        return $this->hasMany(Comuna::class);
    }

    public function documentosDte()
    {
        // Documentos cuyo receptor pertenece a esta region.
        return $this->hasMany(DocumentoDte::class);
    }
}
