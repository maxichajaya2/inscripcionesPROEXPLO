<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Cuota;
use App\Models\Facturacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentosController extends Controller
{
    public function index()
    {
        $contratos = Contrato::where('id_usuario_ecommerce', auth()->user()->id)->where('isactive', true)->get();
        return Inertia::render('Documentos/Index', compact('contratos'));
    }

    public function documentContrato($id)
    {
        $contrato = Contrato::find($id);
        if ($contrato != null) {
            $facturacion = Facturacion::with('moneda')->find($contrato->id_facturacion);
            $cuota = Cuota::where('id_facturacion', $facturacion->id)->where('isactive',true)->first();
            return [
                'status' => true,
                'contrato' => $contrato->contenido,
                'facturacion' => $facturacion,
                'cuota' => $cuota
            ];
        }
        return [
            'status' => false
        ];
    }
}
