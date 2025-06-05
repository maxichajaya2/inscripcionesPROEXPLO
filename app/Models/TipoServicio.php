<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoServicio extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = 'tipo_servicio';


    public function tipo_pago(): BelongsToMany
    {
        return $this->belongsToMany(TipoPago::class);
    }
}

