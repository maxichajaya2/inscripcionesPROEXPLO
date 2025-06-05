<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoriaInscripcion extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "categoria_inscripcion";

    public function precio(): BelongsTo
    {
        return $this->belongsTo(Precio::class, 'id_precio');
    }
}
