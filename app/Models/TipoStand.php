<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoStand extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "tipo_stand";

    public function Precio(): BelongsTo
    {
        return $this->belongsTo(Precio::class, 'id_precio')->where('isactive', 1);
    }

    public function medida(): BelongsTo
    {
        return $this->belongsTo(Medida::class, 'id_medida')->where('isactive', 1);
    }
}
