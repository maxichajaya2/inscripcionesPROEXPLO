<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TipoDocumentoPago;
use App\Models\TipoDocumento;
use App\Models\Cuota;

class Facturacion extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";
    protected $table = "facturacion";

    protected $fillable = [
        'id_tipo_servicio',
        'id_moneda',
        'id_tipo_pago',
        'tipo_doc_pago',
        'id_tipo_doc_facturador',
        'numero_doc_facturador',
        'nombre_facturador',
        'direccion_facturador',
        'correo_facturador',
        'celular_facturador',
        'id_comprador',
        'tipo_comprador',
        'sub_total',
        'IGV',
        'detraccion',
        'total',
        'observacion',
    ];

    public function tipoDocumentoPago(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoPago::class, 'tipo_doc_pago')->where('isactive', 1);
    }

    public function tipoDocumentoFacturador(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_doc_facturador')->where('isactive', 1);
    }

    public function tipoPago(): BelongsTo
    {
        return $this->belongsTo(TipoPago::class, 'id_tipo_pago')->where('isactive', 1);
    }

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'id_moneda')->where('isactive', 1);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class,'id_facturacion', 'id')->where('isactive', 1)->orderBy('cuotas.id','ASC');
    }
}
