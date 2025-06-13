<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoInscripcionBeneficio extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "tipo_inscripcion_beneficio";

    public function precio(): BelongsTo
    {
        return $this->belongsTo(Precio::class, 'id_precio', 'id');
    }
}
