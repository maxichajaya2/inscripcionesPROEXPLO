<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Recibida - {{ config('app.event_name') }}</title>
</head>

<body style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #f4f7f9; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 15px; overflow: hidden; shadow: 0 4px 12px rgba(0,0,0,0.05); border-top: 6px solid #002855;">

                    {{-- CABECERA --}}
                    <tr>
                        <td style="padding: 30px 40px; text-align: center; background-color: #ffffff;">
                            <img src="https://inscripciones.proexplo.com.pe/images/logo-proexplo.webp" alt="Logo"
                                width="180" style="margin-bottom: 20px;">
                            <h2 style="color: #f97316; margin: 0; font-size: 22px; text-transform: uppercase;">¡Gracias
                                por contactarnos!</h2>
                        </td>
                    </tr>

                    {{-- MENSAJE PRINCIPAL --}}
                    <tr>
                        <td style="padding: 0 40px 20px 40px; color: #475569; font-size: 16px; line-height: 1.6;">
                            <p>Hola <strong>{{ $contacto->nombres }}</strong>,</p>
                            <p>Hemos recibido tu mensaje correctamente. Nuestro equipo revisará tu consulta y se pondrá
                                en contacto contigo a la brevedad posible.</p>
                        </td>
                    </tr>

                    {{-- RESUMEN DE LA CONSULTA --}}
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <div
                                style="background-color: #f8fafc; border-radius: 10px; padding: 20px; border: 1px solid #e2e8f0;">
                                <h3
                                    style="color: #f97316; font-size: 14px; margin-top: 0; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                    Detalles de tu mensaje:</h3>

                                <table width="100%" style="font-size: 14px; color: #1e293b;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b; width: 35%;">Nombres:</td>
                                        <td style="padding: 8px 0; font-weight: 600;">{{ $contacto->nombres }}
                                            {{ $contacto->apellidos }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">Email:</td>
                                        <td style="padding: 8px 0; font-weight: 600;">{{ $contacto->email }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">Teléfono:</td>
                                        <td style="padding: 8px 0; font-weight: 600;">
                                            {{ $contacto->telefono ?? 'No especificado' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="padding: 15px 0 5px 0; color: #64748b;">Mensaje:</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"
                                            style="padding: 10px; background-color: #ffffff; border-radius: 6px; border: 1px solid #f1f5f9; color: #334155; font-style: italic;">
                                            "{{ $contacto->mensaje }}"
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- PIE DE PÁGINA INTERNO --}}
                    <tr>
                        <td style="padding: 0 40px 30px 40px; border-top: 1px dashed #f1f5f9;">
                            <p style="color: #64748b; font-weight: 500; margin: 0; font-size: 13px; padding-top: 15px;">
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

                {{-- CRÉDITOS --}}
                <table width="100%" style="max-width: 600px; margin-top: 20px; text-align: center;">
                    <tr>
                        <td style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">
                            &copy; {{ date('Y') }} {{ config('app.event_name') }}. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
