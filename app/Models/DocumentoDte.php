<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoDte extends Model
{
    use HasFactory;

    protected $table = 'documentos_dte';

    protected $fillable = [
        'tipo_dte_id',
        'user_id',
        'folio',
        'fecha_emision',
        'rut_emisor',
        'razon_social_emisor',
        'rut_receptor',
        'razon_social_receptor',
        'giro_receptor',
        'direccion_receptor',
        'region_id',
        'comuna_id',
        'monto_neto',
        'iva',
        'monto_total',
        'timbre',
        'xml_generado',
        'pdf_path',
        'track_id',
        'estado_sii',
        'codigo_respuesta_sii',
        'mensaje_respuesta_sii',
        'fecha_envio_sii',
        'fecha_respuesta_sii',
        'fecha_envio_receptor',
        'estado_receptor',
        'fecha_respuesta_receptor',
        'observacion_receptor',
        'transporte_patente',
        'transporte_rut_chofer',
        'transporte_nombre_chofer',
        'direccion_destino',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_envio_sii' => 'datetime',
        'fecha_respuesta_sii' => 'datetime',
        'fecha_envio_receptor' => 'datetime',
        'fecha_respuesta_receptor' => 'datetime',
    ];

    public function tipoDte()
    {
        return $this->belongsTo(TipoDte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class);
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
