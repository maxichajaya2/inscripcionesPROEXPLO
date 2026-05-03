<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalCarbono extends Model
{
    use HasFactory;

    // Mantengo tu conexión secundaria basándome en el ejemplo
    protected $connection = "pgsql_second";

    protected $table = "personal_carbono";

    // Solo 'nombre' y 'correo' porque created_at y updated_at los maneja Laravel automáticamente
    protected $fillable = [
        'nombre',
        'correo',
        'enviado'
    ];

}
