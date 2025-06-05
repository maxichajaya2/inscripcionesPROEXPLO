<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoPago extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = 'tipo_pago';

    public function tipo_servicio(): BelongsToMany
    {
        return $this->belongsToMany(TipoServicio::class);
    }
}
