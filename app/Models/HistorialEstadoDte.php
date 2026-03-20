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

    // Tabla que conserva cada cambio de estado del DTE.
    protected $table = 'historial_estados_dte';

    // Datos necesarios para auditar la transicion.
    protected $fillable = ['documento_dte_id', 'estado_anterior', 'estado_nuevo', 'user_id', 'comentario'];

    public function documento()
    {
        // Documento afectado por el cambio.
        return $this->belongsTo(DocumentoDte::class);
    }

    public function user()
    {
        // Usuario que ejecuta la accion.
        return $this->belongsTo(User::class);
    }
}
