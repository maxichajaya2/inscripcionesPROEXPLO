<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Stand extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "stand";

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'reserved_by');
    }

    public function tipo_stand(): BelongsTo
    {
        return $this->belongsTo(TipoStand::class, 'id_tipo_stand')->where('isactive', 1);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'id_zona')->where('isactive', 1);
    }

    public function precio(): BelongsTo
    {
        return $this->belongsTo(Precio::class, 'id_precio')->where('isactive', 1);
    }

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'id', 'id_stand');
    }

    public function contrato(): BelongsToMany
    {
        return $this->belongsToMany(Contrato::class, 'detalle_contrato', 'id_stand', 'id_contrato');
    }

    public function Medida():BelongsTo
    {
        return $this->belongsTo(Medida::class, 'id_medida')->where('isactive', 1);
    }
}
