<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    use HasFactory;
    
    protected $connection = "pgsql";
    protected $table = "direccion";

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'id_pais')->where('isactive', 1);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento')->where('isactive', 1);
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'id_provincia')->where('isactive', 1);
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class, 'id_distrito')->where('isactive', 1);
    }
}
