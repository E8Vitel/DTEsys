<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Catalogo territorial de comunas.
class Comuna extends Model
{
    use HasFactory;

    // Tabla donde se almacenan las comunas.
    protected $table = 'comunas';

    protected $fillable = ['region_id', 'nombre'];

    public function region()
    {
        // Region a la que pertenece la comuna.
        return $this->belongsTo(Region::class);
    }

    public function documentosDte()
    {
        // Documentos cuyo receptor pertenece a esta comuna.
        return $this->hasMany(DocumentoDte::class);
    }
}
