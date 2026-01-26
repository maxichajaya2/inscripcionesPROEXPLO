<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Perfil extends Model
{
    protected $connection = 'pgsql_second';
    protected $table = "perfiles";

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(
            CategoriaCursoViaje::class,
            'detalle_categoria_cursos_viajes',
            'id_perfil',
            'id_categoria_cursos_viajes'
        );
    }
}
