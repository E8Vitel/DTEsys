<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Firma digital simulada (texto o canvas)
 */
class FirmaDte extends Model
{
    use HasFactory;

    protected $table = 'firmas_dte';

    protected $fillable = ['documento_dte_id', 'tipo_firma', 'firma_texto', 'firma_canvas', 'user_id'];

    public function documento()
    {
        return $this->belongsTo(DocumentoDte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}