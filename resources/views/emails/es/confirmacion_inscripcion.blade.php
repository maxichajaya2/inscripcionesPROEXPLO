@php
    $dias_nombres = [
        'lun' => 'Lunes',
        'mar' => 'Martes',
        'mie' => 'Miércoles',
        'jue' => 'Jueves',
        'vie' => 'Viernes',
    ];
    $dias_seleccionados = [];

    $nombre_cat_es = strtoupper($inscripcion->categoria_inscripcion->nombre_es);
    $nombre_cat_en = strtoupper($inscripcion->categoria_inscripcion->nombre_en);

    $es_estudiante = str_contains($nombre_cat_en, 'STUDENT') || str_contains($nombre_cat_es, 'ESTUDIANTE');

    if (
        !empty($inscripcion->dias) &&
        !$es_estudiante &&
        (str_contains($nombre_cat_es, ' DIA') || str_contains($nombre_cat_en, ' DAY'))
    ) {
        $dias_inscripcion = json_decode($inscripcion->dias, true);
        if (is_array($dias_inscripcion)) {
            foreach ($dias_inscripcion as $key => $dia) {
                if ($dia == 1) {
                    $dias_seleccionados[] = $dias_nombres[$key];
                }
            }
        }
    }

    $digitos = substr($pago->card_num, -4);
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Inscripción - PROEXPLO 2026</title>
</head>

<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f0f4f8; margin: 0; padding: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #f0f4f8; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 8px solid #f97316;">

                    {{-- CABECERA --}}
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <img src="https://inscripciones.proexplo.com.pe/images/logo-proexplo.webp"
                                alt="PROEXPLO 2026" width="220" style="display: block; margin: 0 auto 25px auto;">
                            <h1
                                style="color: #001e3d; font-size: 26px; font-weight: 800; margin: 0; text-transform: uppercase;">
                                Inscripción Confirmada</h1>
                            <div style="width: 80px; height: 4px; background-color: #22c55e; margin: 15px auto;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 30px 40px; text-align: center; color: #334155; line-height: 1.6;">
                            <p style="font-size: 18px; margin: 0;">Estimado(a)
                                <strong>{{ $inscripcion->persona->nombres }}</strong>,
                            </p>
                            <p style="font-size: 16px; margin: 10px 0 0 0;">Nos complace informarle que su inscripción
                                para <strong>{{ config('app.event_name') }}</strong> ha sido procesada con éxito.</p>
                        </td>
                    </tr>

                    {{-- DETALLES DEL PARTICIPANTE --}}
                    <tr>
                        <td style="padding: 0 40px;">
                            <div
                                style="background-color: #f8fafc; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0;">
                                <h3
                                    style="color: #f97316; font-size: 14px; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                    Detalles del Participante
                                </h3>

                                <table width="100%" style="font-size: 14px; color: #334155;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b; width: 40%;">Nombre Completo:</td>
                                        <td style="padding: 5px 0; font-weight: 600;">
                                            {{ $inscripcion->persona->nombres ?? '' }}
                                            {{ $inscripcion->persona->apellido_paterno ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">
                                            {{ $inscripcion->persona->tipoDocumento->nombre ?? 'Documento' }}:
                                        </td>
                                        <td style="padding: 5px 0; font-weight: 600;">
                                            {{ $inscripcion->persona->documento ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Teléfono:</td>
                                        <td style="padding: 5px 0; font-weight: 600;">
                                            {{ $inscripcion->persona->celular ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Categoría:</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #001e3d;">
                                            {{ $inscripcion->categoria_inscripcion->nombre_es ?? '' }}
                                        </td>
                                    </tr>
                                    @if (count($dias_seleccionados) > 0)
                                        <tr>
                                            <td style="padding: 5px 0; color: #64748b;">Días Seleccionados:</td>
                                            <td style="padding: 5px 0;">
                                                @foreach ($dias_seleccionados as $dia)
                                                    <span
                                                        style="background: #22c55e; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-right: 4px;">
                                                        {{ $dia }}
                                                    </span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 40px 0 40px;">
                            <div
                                style="background-color: #f8fafc; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0;">
                                <h3
                                    style="color: #f97316; font-size: 14px; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                    Resumen del Evento y Actividades Adicionales
                                </h3>

                                <table width="100%" style="font-size: 14px; color: #334155;">
                                    <thead>
                                        <tr style="color: #64748b; font-size: 12px; text-transform: uppercase;">
                                            <th align="left" style="padding-bottom: 10px;">Descripción</th>
                                            <th align="right" style="padding-bottom: 10px;">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // 1. PRIMERO CALCULAMOS LOS EXTRAS PARA TENER EL SUBTOTAL
                                            $extras_ids = $inscripcion->id_categoria_cursos_viajes ?? [];
                                            $perfil_id =
                                                $inscripcion->categoria_inscripcion->precio->first()->pivot
                                                    ->id_perfil ?? null;

                                            $extras = \App\Models\CategoriaCursoViaje::whereIn('id', $extras_ids)
                                                ->with([
                                                    'precios' => function ($query) use ($perfil_id) {
                                                        $query->where(
                                                            'detalle_categoria_cursos_viajes.id_perfil',
                                                            $perfil_id,
                                                        );
                                                    },
                                                ])
                                                ->get();

                                            $subtotal_extras = 0;
                                            foreach ($extras as $e) {
                                                $subtotal_extras += $e->precios->first()->valor ?? 0;
                                            }

                                            // 2. AHORA CALCULAMOS EL COSTO BASE
                                            $total_factura = (float) $inscripcion->facturacion->total;
                                            $costo_base = $total_factura - $subtotal_extras;
                                        @endphp

                                        {{-- MOSTRAR REGISTRO BASE --}}
                                        @if ($costo_base > 0)
                                            <tr>
                                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                                                    <span
                                                        style="font-weight: 600; color: #001e3d; display: block;">Inscripción
                                                        al evento</span>
                                                    <span
                                                        style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">
                                                        {{ $inscripcion->categoria_inscripcion->nombre_es ?? '' }}
                                                    </span>
                                                </td>
                                                <td align="right"
                                                    style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #334155;">
                                                    USD {{ number_format($costo_base, 2) }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- MOSTRAR EXTRAS --}}
                                        @foreach ($extras as $extra)
                                            @php $precio_item = $extra->precios->first()->valor ?? 0; @endphp
                                            <tr>
                                                <td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                                                    <span
                                                        style="font-weight: 600; color: #001e3d; display: block;">{{ $extra->nombre_es ?? '' }}</span>
                                                    <span
                                                        style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Actividad
                                                        Adicional</span>
                                                </td>
                                                <td align="right"
                                                    style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #334155;">
                                                    USD {{ number_format($precio_item, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- RESUMEN DE PAGO --}}
                    <tr>
                        <td style="padding: 20px 40px 40px 40px;">
                            <div style="background-color: #001e3d; border-radius: 12px; padding: 25px; color: #ffffff;">
                                <h3
                                    style="color: #f97316; font-size: 14px; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                                    Resumen de Facturación
                                </h3>

                                <table width="100%" style="font-size: 14px;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">ID Transacción:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            #{{ $pago->idtransaccion ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Tarjeta:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            •••• {{ $digitos ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Razón Social / Nombre:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->nombre_facturador ?? '' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">RUC / N° Documento:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->numero_doc_facturador ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Dirección:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->direccion_facturador ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Persona de Contacto:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->responsable_facturador ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Correo de Facturación:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->correo_facturador ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Método de Pago:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->tipoPago->nombre ?? 'Niubiz Tarjeta' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 5px 0; color: #cbd5e1;">Tipo de Comprobante:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->tipoDocumentoPago->nombre ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="padding: 20px 0 0 0; font-size: 18px; font-weight: 800; color: #f97316;">
                                            TOTAL PAGADO:</td>
                                        <td
                                            style="padding: 20px 0 0 0; font-size: 22px; font-weight: 900; text-align: right; color: #22c55e;">
                                            USD {{ number_format($inscripcion->facturacion->total, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- QR CODE --}}
                    @if ($inscripcion->qr)
                        <tr>
                            <td style="padding: 0 40px 40px 40px; text-align: center;">
                                <div
                                    style="display: inline-block; padding: 20px; background-color: #ffffff; border: 2px solid #f1f5f9; border-radius: 16px;">
                                    <p
                                        style="margin: 0 0 10px 0; font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase;">
                                        Pase de Entrada / Código QR</p>
                                    <img src="{{ $qr_url }}" alt="Código QR" width="160" height="160">
                                </div>
                                <p style="margin: 15px 0 0 0; font-size: 13px; color: #64748b;">Presente este código en
                                    el mostrador de registro al llegar al evento.</p>
                            </td>
                        </tr>
                    @endif

                    {{-- CONTACTO --}}
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="border-top: 1px solid #e2e8f0; padding-top: 25px; text-align: center;">

                                <tr>
                                    <td style="padding-bottom: 10px;">
                                        <p style="color: #64748b; font-weight: 500; margin: 0; font-size: 13px;">
                                            Para alojamiento con tarifas preferenciales:
                                        </p>
                                        <p style="margin: 5px 0 20px 0;">
                                            <a href="mailto:reservas@iimp.org.pe"
                                                style="color: #f97316; text-decoration: none; font-weight: bold; font-size: 14px; margin-right: 15px;">
                                                <span style="font-size: 16px;">✉</span> reservas@iimp.org.pe
                                            </a>
                                            <a href="https://wa.me/51942797524" target="_blank"
                                                style="color: #22c55e; text-decoration: none; font-weight: bold; font-size: 14px;">
                                                <span style="font-size: 16px;">📱</span> +51 942 797 254 (Melisa Ramos)
                                            </a>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding-top: 10px; border-top: 1px dashed #f1f5f9;">
                                        <p
                                            style="color: #64748b; font-weight: 500; margin: 0; font-size: 13px; padding-top: 15px;">
                                            Para cualquier consulta adicional, contáctenos:
                                        </p>
                                        <p style="margin: 5px 0 0 0;">
                                            <a href="mailto:inscripciones.wmc@iimp.org.pe"
                                                style="color: #f97316; text-decoration: none; font-weight: bold; font-size: 14px; margin-right: 15px;">
                                                <span style="font-size: 16px;">✉</span> inscripciones.wmc@iimp.org.pe
                                            </a>
                                            <a href="https://wa.me/51951294314" target="_blank"
                                                style="color: #22c55e; text-decoration: none; font-weight: bold; font-size: 14px;">
                                                <span style="font-size: 16px;">📱</span> +51 951 294 314 (Helen Loaiza)
                                            </a>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="padding: 0 40px 40px 40px; text-align: center; font-size: 13px; color: #94a3b8; line-height: 1.5;">
                            <p>Gracias por ser parte de <strong>{{ config('app.event_name') }}</strong>.</p>
                            <p style="margin-top: 15px; font-size: 11px; color: #cbd5e1;">Este es un mensaje
                                automático.
                                Por favor, no responda a este correo. Si tiene alguna pregunta, póngase en contacto con
                                nuestro equipo de soporte.</p>
                        </td>
                    </tr>
                </table>

                <table width="100%" style="max-width: 600px; margin-top: 20px; text-align: center;">
                    <tr>
                        <td style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                            &copy; {{ date('Y') }} {{ config('app.event_name') }}. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
