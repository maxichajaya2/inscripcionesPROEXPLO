<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";
    protected $table = "cuotas";

    protected $fillable = [
        'informacion',
        'respuesta_api',
        'isactive',
        'id_facturacion',
        'idioma'
    ];

    protected $casts = [
        'informacion' => 'array',
    ];

    public function facturacion()
    {
        return $this->belongsTo(\App\Models\Facturacion::class, 'id_facturacion');
    }
}
