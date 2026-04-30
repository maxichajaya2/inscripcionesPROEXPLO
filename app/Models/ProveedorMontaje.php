<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorMontaje extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";

    protected $table = "proveedores_montaje";

    protected $fillable = [
        'nombre_empresa',
        'email_principal',
        'emails_cc',
        'is_active',
        'last_sent_at',
    ];

    // Aquí está la magia para que PostgreSQL y Laravel se entiendan con el JSONB
    protected $casts = [
        'emails_cc' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];
}
