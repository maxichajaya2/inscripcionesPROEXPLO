<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscripcion;
use Inertia\Inertia;

class InscritosController extends Controller
{
    public function index()
    {
        // Traemos las inscripciones con sus relaciones y las ordenamos por las más recientes
        $inscripciones = Inscripcion::with(['persona', 'facturacion' ,'cupon'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'id' => $inscripcion->id,
                    'fecha_registro' => $inscripcion->created_at ? $inscripcion->created_at->format('d/m/Y H:i') : '-',
                    'origen' => $inscripcion->origen ?? 'web',
                    'estado' => $inscripcion->isactive,
                    'cargo' => $inscripcion->texto_cargo ?? 'No especificado',

                    // Datos de Persona (usando operador seguro ?-> por si es null)
                    'nombres' => trim(($inscripcion->persona?->nombres ?? '') . ' ' . ($inscripcion->persona?->apellidos ?? '')) ?: 'Sin nombre',
                    'documento' => $inscripcion->persona?->dni ?? $inscripcion->persona?->num_documento ?? 'Sin doc',
                    'email' => $inscripcion->persona?->email ?? 'Sin correo',

                    // Datos de Facturación (usando operador seguro ?->)
                    'tiene_factura' => $inscripcion->id_facturacion ? true : false,
                    'factura_ruc' => $inscripcion->facturacion?->ruc ?? '-',
                    'factura_razon_social' => $inscripcion->facturacion?->razon_social ?? 'No requiere factura',
                ];
            });

        return Inertia::render('Admin/Inscritos/Index', [
            'inscritos' => $inscripciones
        ]);
    }
}
