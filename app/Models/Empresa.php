<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Empresa extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = "empresa";

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'id_direccion');
    }

    public function rubro() : BelongsTo
    {
      return $this->belongsTo(Rubro::class, 'id_rubro')->where('isactive', 1);
    }

    public function permisos(): BelongsToMany
    {
      return $this->belongsToMany(Permiso::class, 'detalle_permiso_empresa', 'id_empresa', 'id_permiso')->where('isactive', 1)->whereJsonContains('tipo', 'empresa');
    }

    public function tipo_documento() : BelongsTo
    {
      return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento')->where('isactive', 1);
    }

    public function stands() : HasMany
    {
      return $this->hasMany(Stand::class, 'reserved_by','id')->where('isactive', 1);
    }
}
