<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocupacion extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = 'ocupacion';

    protected $fillable = [
        'id',           // IMPORTANTE: Como usas el ID de la API, debe ser fillable
        'name',
        'descripcion',
        'isactive'
    ];


}
