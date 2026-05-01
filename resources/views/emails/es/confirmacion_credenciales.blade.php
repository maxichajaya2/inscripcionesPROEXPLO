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
    <title>Credenciales - PROEXPLO 2026</title>
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
                                Credenciales de Acceso</h1>
                            <div style="width: 80px; height: 4px; background-color: #22c55e; margin: 15px auto;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 30px 40px; text-align: center; color: #334155; line-height: 1.6;">
                            <p style="font-size: 18px; margin: 0;">Estimado(a)
                                <strong>{{ $inscripcion->persona->nombres }}</strong>,
                            </p>
                            <p style="font-size: 16px; margin: 10px 0 0 0;">Nos complace informarle que sus credenciales de acceso
                                para <strong>{{ config('app.event_name') }}</strong> han sido procesadas con éxito.</p>
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
                                            {{ $inscripcion->persona->tipoDocumento->name_es ?? 'Documento' }}:
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
                                </table>
                            </div>
                        </td>
                    </tr>


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
                                                <span style="font-size: 16px;">✉</span> inscripciones@iimp.org.pe
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
