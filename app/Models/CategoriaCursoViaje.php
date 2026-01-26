<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoriaCursoViaje extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second'; // Usando tu conexión de base de datos
    protected $table = "categoria_cursos_viajes";

    // Seguimos la misma lógica que CategoriaInscripcion
    public function precios(): BelongsToMany
    {
        return $this->belongsToMany(
            Precio::class,
            'detalle_categoria_cursos_viajes', // Tu tabla intermedia
            'id_categoria_cursos_viajes',      // FK de esta tabla
            'id_precio'                        // FK de precios
        )->withPivot('id_perfil');;
    }

    // También necesitamos relacionarlo con Perfiles
    public function perfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            Perfil::class,
            'detalle_categoria_cursos_viajes',
            'id_categoria_cursos_viajes',
            'id_perfil'
        );
    }
}
