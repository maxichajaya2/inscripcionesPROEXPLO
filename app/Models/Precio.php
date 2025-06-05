<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Moneda;

class Precio extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";
    protected $table = "precio";

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'id_moneda')->where('isactive', 1);
    }

}
