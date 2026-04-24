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
        // 1. OPTIMIZACIÓN CRÍTICA: Traemos todas las categorías a memoria UNA SOLA VEZ.
        // Esto evita hacer una consulta a la base de datos por cada inscrito dentro del map().
        $todasLasCategorias = \App\Models\CategoriaCursoViaje::select('id', 'nombre_es', 'tipo')
            ->get()
            ->keyBy('id');

        // 2. CONSULTA PRINCIPAL
        $inscripciones = Inscripcion::with([
            'persona.tipoDocumento',
            'facturacion.tipoDocumentoFacturador',
            'cupon',
            'facturacion.cuotas.niubiz' => function ($query) {
                // Filtramos para que solo traiga los registros pagados de Niubiz y del evento 6
                $query->where('estado', 'pagado')
                    ->where('id_evento', 6);
            }
        ])
            ->orderBy('id', 'desc')
            ->get() // <-- Traemos los datos primero

            // 3. MAGIA AQUÍ: Filtramos la colección en memoria para evitar el error de PostgreSQL
            ->filter(function ($inscripcion) {
                // Si no tiene facturación o cuotas, lo descartamos
                if (!$inscripcion->facturacion || !$inscripcion->facturacion->cuotas) {
                    return false;
                }
                // Nos quedamos SOLO con los que tengan al menos una cuota con registro en Niubiz
                return $inscripcion->facturacion->cuotas->contains(fn($c) => $c->niubiz !== null);
            })

            // 4. MAPEO DE DATOS PARA VUE
            ->map(function ($inscripcion) use ($todasLasCategorias) {
                // Buscamos la primera cuota que tenga un registro de Niubiz
                $cuotaPagada = $inscripcion->facturacion?->cuotas->first(fn($c) => $c->niubiz !== null);
                $pagoNiubiz = $cuotaPagada?->niubiz;

                // --- LÓGICA DE TARJETA CON TRIM() PARA EVITAR ESPACIOS EN BLANCO ---
                $cardNum = trim($pagoNiubiz?->card_num ?? '');
                $marcaTarjeta = 'Otra/Desconocida';

                if ($cardNum) {
                    if (str_starts_with($cardNum, '4')) {
                        $marcaTarjeta = 'Visa';
                    } elseif (str_starts_with($cardNum, '5') || str_starts_with($cardNum, '2')) {
                        $marcaTarjeta = 'Mastercard';
                    } elseif (str_starts_with($cardNum, '34') || str_starts_with($cardNum, '37')) {
                        $marcaTarjeta = 'American Express';
                    } elseif (str_starts_with($cardNum, '36') || str_starts_with($cardNum, '38')) {
                        $marcaTarjeta = 'Diners Club';
                    } elseif (str_starts_with($cardNum, '62')) {
                        $marcaTarjeta = 'UnionPay';
                    }
                }
                // -------------------------------------------------------------------

                // --- LÓGICA OPTIMIZADA DE CURSOS/VIAJES (Sin tocar la BD) ---
                $cursosViajes = [];
                if (!empty($inscripcion->id_categoria_cursos_viajes)) {
                    foreach ($inscripcion->id_categoria_cursos_viajes as $idCurso) {
                        if (isset($todasLasCategorias[$idCurso])) {
                            $cursosViajes[] = $todasLasCategorias[$idCurso];
                        }
                    }
                }
                // -------------------------------------------------------------------

                return [
                    'id' => $inscripcion->id,
                    'fecha_registro' => $inscripcion->created_at ? $inscripcion->created_at->format('d/m/Y H:i') : '-',
                    'estado_inscripcion' => $inscripcion->isactive,
                    'origen' => $inscripcion->origen ?? 'Web',
                    'cargo' => $inscripcion->texto_cargo ?? 'No especificado',
                    'qr'=> $inscripcion->qr ?? null,
                    'cupon_viaje' => $inscripcion->cupon_viaje ?? null,

                    // Variables de tarjeta
                    'marca_tarjeta' => $marcaTarjeta,
                    'numero_tarjeta' => $cardNum,

                    // Datos Completos de Persona
                    'persona' => $inscripcion->persona,
                    'nombres' => trim(($inscripcion->persona?->nombres ?? '') . ' ' . ($inscripcion->persona?->apellidos ?? '')),
                    'documento' => $inscripcion->persona?->dni ?? $inscripcion->persona?->documento ?? '-',
                    'tipo_documento' => $inscripcion->persona?->tipoDocumento?->name_es ?? 'Sin tipo',
                    'email' => $inscripcion->persona?->correo ?? 'Sin correo',

                    // Datos de Facturación Detallados
                    'facturacion' => [
                        'monto_total' => $inscripcion->facturacion?->total ?? 0,
                        'sub_total' => $inscripcion->facturacion?->sub_total ?? 0,
                        'igv' => $inscripcion->facturacion?->IGV ?? 0,
                        'razon_social' => $inscripcion->facturacion?->nombre_facturador ?? '-',
                        'ruc' => $inscripcion->facturacion?->numero_doc_facturador ?? '-',
                        'direccion' => $inscripcion->facturacion?->direccion_facturador ?? '-',
                        'correo_facturador' => $inscripcion->facturacion?->correo_facturador ?? '-',
                        'tipo_documento' => $inscripcion->facturacion?->tipoDocumentoFacturador?->name_es ?? 'RUC',
                    ],

                    'categoria_inscripcion' => $inscripcion->categoria_inscripcion ? [
                        'nombre_es' => $inscripcion->categoria_inscripcion->nombre_es
                    ] : null,

                    // Usamos el arreglo optimizado en memoria
                    'categoria_cursos_viajes' => $cursosViajes,

                    // Cupon
                    'cupon' => $inscripcion->cupon ? [
                        'codigo' => $inscripcion->cupon->codigo_cupon,
                        'razon_social' => $inscripcion->cupon->razon_social ?? 'Empresa no registrada'
                    ] : null,

                    // Pagos
                    'pagos' => $inscripcion->facturacion?->cuotas
                        ->filter(fn($cuota) => $cuota->niubiz !== null)
                        ->map(function ($cuota) {
                            return [
                                'cuota_id' => $cuota->id,
                                'estado_niubiz' => $cuota->niubiz->estado,
                                'transaccion_id' => $cuota->niubiz->id,
                                'info_pago' => $cuota->informacion,
                                'respuesta_api' => $cuota->respuesta_api,
                            ];
                        })->values(),

                    // Estado simplificado para la tabla
                    'estado_pago' => 'PAGADO', // Como ya filtramos los pendientes arriba, aquí todos son PAGADOS
                ];
            })
            ->values(); // <-- IMPORTANTE: values() reordena los índices tras el filter() inicial


        return \Inertia\Inertia::render('Admin/Inscritos/Index', [
            'inscritos' => $inscripciones
        ]);
    }
}
