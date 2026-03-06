<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    use HasFactory;

    protected $table = 'comunas';

    protected $fillable = ['region_id', 'nombre'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function documentosDte()
    {
        return $this->hasMany(DocumentoDte::class);
    }
}
