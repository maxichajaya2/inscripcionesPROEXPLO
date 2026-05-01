<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalMontaje extends Model
{
    use HasFactory;

    // Indicamos el nombre exacto de la tabla en la base de datos
    protected $table = 'personal_montaje';

    // Los campos que se pueden llenar masivamente (Mass Assignment)
    protected $fillable = [
        'codigo_qr',
        'documento',
        'nombres',
        'apellidos',
        'correo',
        'cargo',
        'ruc_empresa',
        'nombre_empresa',
        'autorizado',
        'motivo_bloqueo',
        'estado_presencia',
        'tipo_documento', // Agregado para el nuevo campo
        'aseguradora', // Opcional, por si decides agregar este campo más adelante
        'poliza', // Opcional, por si decides agregar este campo más adelante
        'fotografia_url' // Opcional, por si decides agregar fotos más adelante
    ];

    // Convertimos automáticamente los booleanos
    protected $casts = [
        'autorizado' => 'boolean',
    ];
}
