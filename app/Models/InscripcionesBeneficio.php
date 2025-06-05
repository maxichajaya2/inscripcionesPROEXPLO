<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscripcionesBeneficio extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "inscripciones_beneficio";

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoInscripcionBeneficio::class, 'id_tipo_inscripcion_beneficio', 'id');
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id');
    }
}
