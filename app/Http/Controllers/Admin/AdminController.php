<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cupon;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Inscripcion;
use Carbon\Carbon;

class AdminController extends Controller
{
    // public function index()
    // {

    //     $inscripcion = Inscripcion::with(['persona', 'facturacion'])->first();

    //     // 1. Estadísticas Generales (Tarjetas)
    //     $stats = [
    //         'total_usuarios' => User::count(),
    //         'total_roles' => Role::count(),
    //         'cupones_activos' => Cupon::where('is_active', true)
    //             ->where('fecha_fin', '>', now())
    //             ->where('fecha_inicio', '<=', now())
    //             ->where('is_delete', false)
    //             ->count(),
    //         'usos_totales_cupones' => Cupon::where('is_active', true)
    //             ->where('is_delete', false)
    //             ->where('fecha_inicio', '<=', now())
    //             ->where('fecha_fin', '>', now())
    //             ->sum('usos_actuales'),
    //     ];

    //     // 2. Actividad Reciente: Últimos 5 usuarios registrados
    //     $ultimosUsuarios = User::with('roles')
    //         ->latest()
    //         ->take(5)
    //         ->get();

    //     // 3. Actividad Reciente: Últimos 5 cupones creados
    //     $ultimosCupones = Cupon::latest()
    //         ->where('is_active', true)
    //         ->where('fecha_fin', '>', now())
    //         ->where('fecha_inicio', '<=', now())
    //         ->where('is_delete', false)
    //         ->take(5)
    //         ->get();


    //     return inertia('Admin/Index', [
    //         'stats' => $stats,
    //         'ultimosUsuarios' => $ultimosUsuarios,
    //         'ultimosCupones' => $ultimosCupones,
    //     ]);
    // }

    public function index()
    {
        // 1. TRAEMOS TODO Y FILTRAMOS EN MEMORIA
        $todasLasInscripciones = Inscripcion::with([
            'persona',
            'facturacion.cuotas.niubiz' => function ($query) {
                $query->where('estado', 'pagado')->where('id_evento', 6);
            },
            'cupon'
        ])->orderBy('id', 'desc')->get();

        // 2. NOS QUEDAMOS SOLO CON LAS PAGADAS
        $inscripcionesPagadas = $todasLasInscripciones->filter(function ($inscripcion) {
            if (!$inscripcion->facturacion || !$inscripcion->facturacion->cuotas) return false;
            return $inscripcion->facturacion->cuotas->contains(fn($c) => $c->niubiz !== null);
        });

        // FUNCIÓN LIMPIADORA: Quita comas y espacios para que PHP pueda sumar miles correctamente
        $limpiarMonto = function($monto) {
            if (!$monto) return 0;
            return (float) str_replace([',', ' '], '', $monto);
        };

        // 3. CALCULAMOS MÉTRICAS FINANCIERAS (USANDO LA FUNCIÓN LIMPIADORA)
        $ingresosTotales = $inscripcionesPagadas->sum(fn($i) => $limpiarMonto($i->facturacion->total));
        $pagosConCupon = $inscripcionesPagadas->filter(fn($i) => $i->cupon !== null)->count();
        $pagosSinCupon = $inscripcionesPagadas->count() - $pagosConCupon;

        // 4. USO DE TARJETAS
        $usoTarjetas = ['Visa' => 0, 'Mastercard' => 0, 'Amex' => 0, 'Diners' => 0, 'Otras' => 0];
        foreach ($inscripcionesPagadas as $inscripcion) {
            $cuota = $inscripcion->facturacion->cuotas->first(fn($c) => $c->niubiz !== null);
            $cardNum = trim($cuota?->niubiz?->card_num ?? '');
            if ($cardNum) {
                if (str_starts_with($cardNum, '4')) $usoTarjetas['Visa']++;
                elseif (str_starts_with($cardNum, '5') || str_starts_with($cardNum, '2')) $usoTarjetas['Mastercard']++;
                elseif (str_starts_with($cardNum, '34') || str_starts_with($cardNum, '37')) $usoTarjetas['Amex']++;
                elseif (str_starts_with($cardNum, '36') || str_starts_with($cardNum, '38')) $usoTarjetas['Diners']++;
                else $usoTarjetas['Otras']++;
            }
        }

        // --- 5. LA MAGIA DE LA PROYECCIÓN A $300,000 EN INGRESOS ---
        $metaTotal = 300000; // <--- META ACTUALIZADA A 300 MIL
        $fechaFinCampana = Carbon::parse('2026-05-06')->endOfDay();

        $fechaPrimeraVenta = $inscripcionesPagadas->min('created_at');
        $fechaInicio = $fechaPrimeraVenta ? Carbon::parse($fechaPrimeraVenta)->startOfDay() : now()->subDays(15)->startOfDay();

        // Calculamos cuánto deberíamos ingresar por día idealmente
        $diasTotalesCampana = $fechaInicio->diffInDays($fechaFinCampana) ?: 1;
        $metaDiaria = $metaTotal / $diasTotalesCampana;

        // Calculamos el ritmo ACTUAL (Promedio de DINERO diario real hasta hoy)
        $diasTranscurridos = $fechaInicio->diffInDays(now()->startOfDay()) ?: 1;
        $promedioDiarioReal = $ingresosTotales / $diasTranscurridos;

        $fechasProyeccion = [];
        $lineaReal = [];
        $lineaIdeal = [];
        $lineaProyectada = [];

        $acumuladoReal = 0;
        $acumuladoIdeal = 0;
        $acumuladoProyectado = 0;

        $ventasAgrupadas = $inscripcionesPagadas->groupBy(function($i) {
            return Carbon::parse($i->created_at)->format('Y-m-d');
        });

        // Generamos día por día desde el inicio hasta el 6 de mayo
        for ($date = $fechaInicio->copy(); $date->lte($fechaFinCampana); $date->addDay()) {
            $fechaStr = $date->format('Y-m-d');
            $fechasProyeccion[] = $date->translatedFormat('d M');

            // 1. Línea Ideal (La meta sube constante)
            $acumuladoIdeal += $metaDiaria;
            $lineaIdeal[] = round($acumuladoIdeal, 2);

            // 2 y 3. Líneas Reales y Proyectadas
            if ($date->startOfDay()->lte(now()->startOfDay())) {
                // Pasado o Hoy: Sumamos el DINERO LIMPIO del día
                $ventasDelDia = $ventasAgrupadas->get($fechaStr)?->sum(fn($i) => $limpiarMonto($i->facturacion->total)) ?? 0;
                $acumuladoReal += $ventasDelDia;
                $lineaReal[] = round($acumuladoReal, 2);

                if ($date->startOfDay()->eq(now()->startOfDay())) {
                    $lineaProyectada[] = round($acumuladoReal, 2);
                    $acumuladoProyectado = $acumuladoReal;
                } else {
                    $lineaProyectada[] = null;
                }
            } else {
                // Futuro: La proyección avanza sumando el dinero promedio
                $lineaReal[] = null;
                $acumuladoProyectado += $promedioDiarioReal;
                $lineaProyectada[] = round($acumuladoProyectado, 2);
            }
        }

        // 6. ARMAMOS EL ARREGLO DE ESTADÍSTICAS
        $stats = [
            'total_usuarios' => User::count(),
            'total_roles' => Role::count(),
            'cupones_activos' => Cupon::where('is_active', true)->where('fecha_fin', '>', now())->where('is_delete', false)->count(),

            'inscripciones_pagadas' => $inscripcionesPagadas->count(),
            'ingresos_totales' => $ingresosTotales,
            'pagos_con_cupon' => $pagosConCupon,
            'pagos_sin_cupon' => $pagosSinCupon,
            'uso_tarjetas' => $usoTarjetas,

            'proyeccion' => [
                'fechas' => $fechasProyeccion,
                'reales' => $lineaReal,
                'ideal' => $lineaIdeal,
                'proyectada' => $lineaProyectada,
                'total_estimado' => round($acumuladoProyectado, 2)
            ]
        ];

        // 7. ACTIVIDAD RECIENTE
        $ultimosUsuarios = User::with('roles')->latest()->take(5)->get();
        $ultimasInscripciones = $inscripcionesPagadas->take(5)->map(function ($inscripcion) {
            return [
                'id' => $inscripcion->id,
                'nombres' => trim(($inscripcion->persona?->nombres ?? '') . ' ' . ($inscripcion->persona?->apellidos ?? '')),
                'fecha' => $inscripcion->created_at ? Carbon::parse($inscripcion->created_at)->format('d/m/Y H:i') : '-',
                'monto' => $inscripcion->facturacion?->total ?? 0,
            ];
        })->values();

        return inertia('Admin/Index', [
            'stats' => $stats,
            'ultimosUsuarios' => $ultimosUsuarios,
            'ultimasInscripciones' => $ultimasInscripciones,
        ]);
    }
}
