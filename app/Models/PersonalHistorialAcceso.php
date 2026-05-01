<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalHistorialAcceso extends Model
{
    use HasFactory;

    protected $table = 'personal_historial_accesos';

    // Desactivamos los timestamps por defecto de Laravel porque en tu tabla
    // los llamaste 'fecha_hora' en lugar de 'created_at' y 'updated_at'
    public $timestamps = false;

    protected $fillable = [
        'personal_montaje_id', // <--- CORREGIDO AQUÍ
        'tipo_movimiento',
        'fecha_hora',
        'puerta_acceso',
        'usuario_seguridad',
        'acceso_concedido',
        'notas'
    ];

    protected $casts = [
        'acceso_concedido' => 'boolean',
        'fecha_hora' => 'datetime',
    ];

    // Relación: Un registro de historial pertenece a un trabajador
    public function personal()
    {
        // <--- CORREGIDO AQUÍ TAMBIÉN (Laravel ahora buscará 'personal_montaje_id')
        return $this->belongsTo(PersonalMontaje::class, 'personal_montaje_id');
    }
}
