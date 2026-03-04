<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Historial de cambios de estado
 */
class HistorialEstadoDte extends Model
{
    use HasFactory;

    protected $table = 'historial_estados_dte';

    protected $fillable = ['documento_dte_id', 'estado_anterior', 'estado_nuevo', 'user_id', 'comentario'];

    public function documento()
    {
        return $this->belongsTo(DocumentoDte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}