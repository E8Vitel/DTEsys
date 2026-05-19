<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modelo central del sistema: representa un DTE completo con sus datos y relaciones.
class DocumentoDte extends Model
{
    use HasFactory;

    // Nombre real de la tabla en base de datos.
    protected $table = 'documentos_dte';

    // Atributos que pueden cargarse de forma masiva.
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
        'acuse_receptor_token',
        'estado_receptor',
        'fecha_respuesta_receptor',
        'observacion_receptor',
        'transporte_patente',
        'transporte_rut_chofer',
        'transporte_nombre_chofer',
        'direccion_destino',
    ];

    // Conversion automatica de fechas para trabajar con Carbon en Laravel.
    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_envio_sii' => 'datetime',
        'fecha_respuesta_sii' => 'datetime',
        'fecha_envio_receptor' => 'datetime',
        'fecha_respuesta_receptor' => 'datetime',
    ];

    public function tipoDte()
    {
        // Tipo de documento tributario asociado.
        return $this->belongsTo(TipoDte::class);
    }

    public function user()
    {
        // Usuario propietario o creador del documento.
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        // Region de la direccion del receptor.
        return $this->belongsTo(Region::class);
    }

    public function comuna()
    {
        // Comuna de la direccion del receptor.
        return $this->belongsTo(Comuna::class);
    }

    public function detalles()
    {
        // Lineas o items del documento.
        return $this->hasMany(DetalleDte::class);
    }

    public function historial()
    {
        // Trazabilidad completa de cambios de estado.
        return $this->hasMany(HistorialEstadoDte::class);
    }

    public function firma()
    {
        // Firma unica aplicada al documento.
        return $this->hasOne(FirmaDte::class);
    }
}
