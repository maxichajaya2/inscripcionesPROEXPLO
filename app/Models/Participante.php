<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participante extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = "participante";

    protected $fillable = [
        'id_tipo_participante',
        'id_persona',
        'id_empresa',
        'isactive'
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, "id_persona", "id");
    }

    public function tipoParticipante(): BelongsTo
    {
        return $this->belongsTo(TipoParticipante::class, "id_tipo_participante");
    }
}
