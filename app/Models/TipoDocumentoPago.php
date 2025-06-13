<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentoPago extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = "tipo_documento_pago";
}
