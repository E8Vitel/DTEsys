<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo principal Documento DTE
 * Relaciones completas con historial, detalles y firma
 */
class DocumentoDte extends Model
{
    use HasFactory;

    protected $table = 'documentos_dte';

    protected $fillable = [
        'tipo_dte_id', 'user_id', 'folio', 'fecha_emision',
        'rut_emisor', 'razon_social_emisor', 'rut_receptor',
        'razon_social_receptor', 'monto_neto', 'iva', 'monto_total',
        'timbre', 'xml_generado', 'pdf_path'
    ];

    public function tipoDte()
    {
        return $this->belongsTo(TipoDte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDte::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialEstadoDte::class);
    }

    public function firma()
    {
        return $this->hasOne(FirmaDte::class);
    }
}