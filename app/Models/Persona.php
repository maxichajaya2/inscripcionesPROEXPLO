<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Persona extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = "persona";

    protected $fillable = [
        'sie_code',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'id_tipo_documento',
        'documento',
        'sexo',
        'fecha_nacimiento',
        'correo',
        'celular',
        'id_ocupacion',
        'id_direccion',
        'id_nacionalidad',
        'id_empresa',
        'isactive',
    ];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento');
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, "id_direccion", "id");
    }

    public function ocupacion(): BelongsTo
    {
        return $this->belongsTo(Ocupacion::class, "id_ocupacion", "id");
    }
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, "id_empresa", "id");
    }
}
