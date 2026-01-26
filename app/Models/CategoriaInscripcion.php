<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class CategoriaInscripcion extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "categoria_inscripcion";

    public function precio(): BelongsToMany
    {
        // return $this->belongsToMany(Precio::class, 'detalle_categoria', 'id_categoria_inscripcion', 'id_precio');
        return $this->belongsToMany(Precio::class, 'detalle_categoria', 'id_categoria_inscripcion', 'id_precio')
            ->withPivot('id_perfil') // <--- Indicar que traiga esta columna de la tabla intermedia
            ->withTimestamps();
    }
}
