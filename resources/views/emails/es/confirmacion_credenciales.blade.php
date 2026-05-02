<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - PROEXPLO 2026</title>
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
                                Detalle de la Inscripción</h1>
                            <div style="width: 80px; height: 4px; background-color: #22c55e; margin: 15px auto;"></div>
                        </td>
                    </tr>

                    {{-- SALUDO --}}
                    <tr>
                        <td style="padding: 0 40px 25px 40px; text-align: center; color: #334155; line-height: 1.6;">
                            <p style="font-size: 18px; margin: 0 0 8px 0;">Estimado(a)
                                <strong>{{ $inscripcion->persona->nombres }}</strong>,
                            </p>
                            <p style="font-size: 16px; margin: 0;">El Instituto de Ingenieros de Minas del Perú le da una cordial bienvenida a {{ config('app.event_name', 'PROEXPLO 2026') }}.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 30px 40px; text-align: center; color: #334155;">
                            <p style="font-size: 16px; margin: 0;">A continuación, le compartimos sus datos de acceso al evento:</p>
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

                                <table width="100%" style="font-size: 14px; color: #334155; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b; width: 50%;">Nombre Completo:</td>
                                        <td style="padding: 8px 0; font-weight: 600; text-align: right;">
                                            {{ $inscripcion->persona->nombres ?? '' }}
                                            {{ $inscripcion->persona->apellido_paterno ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">
                                            Tipo de Documento:
                                        </td>
                                        <td style="padding: 8px 0; font-weight: 600; text-align: right;">
                                            {{ $inscripcion->persona->tipoDocumento->name_es ?? 'Documento' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">
                                            Número de Documento:
                                        </td>
                                        <td style="padding: 8px 0; font-weight: 600; text-align: right;">
                                            {{ $inscripcion->persona->documento ?? '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">Clave:</td>
                                        <td style="padding: 8px 0; font-weight: 700; color: #001e3d; text-align: right;">
                                            {{ $inscripcion->sie_password ?? '123456' }}
                                        </td>
                                    </tr>

                                    {{-- BOTÓN DE INGRESO AL PORTAL --}}
                                    <tr>
                                        <td colspan="2" style="padding: 20px 10px 10px 10px; text-align: center;">
                                            <p style="margin: 0 0 12px 0; font-size: 14px; color: #475569;">Puede ingresar a su cuenta utilizando el siguiente enlace:</p>
                                            <a href="https://multiperfil.sistemasiimp.org.pe/"
                                                style="display: inline-block; padding: 12px 22px; background-color: #f97316; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                                Ingresar al portal
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- SECCIÓN CÓDIGO QR --}}
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
                            </td>
                        </tr>
                    @endif

                    {{-- CONTACTO --}}
                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <p style="color: #64748b; font-size: 13px; margin: 0 0 10px 0;">Le recomendamos conservar esta información y no compartirla con terceros.</p>
                            <p style="color: #64748b; font-size: 13px; margin: 10px 0 5px 0;">Para cualquier consulta adicional:</p>
                            <p style="margin: 0;">
                                <a href="mailto:inscripciones@iimp.org.pe" style="color: #f97316; text-decoration: none; font-weight: bold;">inscripciones@iimp.org.pe</a>
                            </p>
                        </td>
                    </tr>

                    {{-- PIE DE PÁGINA INTERNO --}}
                    <tr>
                        <td
                            style="padding: 0 40px 40px 40px; text-align: center; font-size: 13px; color: #94a3b8; line-height: 1.5;">
                            <p style="margin: 0;">Gracias por ser parte de <strong>{{ config('app.event_name', 'PROEXPLO 2026') }}</strong>.</p>
                            <p style="margin-top: 15px; font-size: 11px; color: #cbd5e1;">Este es un mensaje automático. Por favor, no responda a este correo.</p>
                        </td>
                    </tr>
                </table>

                {{-- COPYRIGHT EXTERNO --}}
                <table width="100%" style="max-width: 600px; margin-top: 20px; text-align: center;">
                    <tr>
                        <td style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                            &copy; {{ date('Y') }} {{ config('app.event_name', 'PROEXPLO') }}. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
