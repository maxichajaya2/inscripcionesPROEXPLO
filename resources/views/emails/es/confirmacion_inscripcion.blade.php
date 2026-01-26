@php
    $dias_nombres = [
        'lun' => 'Monday',
        'mar' => 'Tuesday',
        'mie' => 'Wednesday',
        'jue' => 'Thursday',
        'vie' => 'Friday',
    ];
    $dias_seleccionados = [];

    if (str_contains($inscripcion->categoria_inscripcion->nombre_es, 'DIA')) {
        $dias_inscripcion = json_decode($inscripcion->dias, true);
        foreach ($dias_inscripcion as $key => $dia) {
            if ($dia) {
                $dias_seleccionados[] = $dias_nombres[$key];
            }
        }
    }

    $digitos = substr($pago->card_num, -4); // Generalmente se muestran los últimos 4
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - WMC</title>
</head>

<body
    style="font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #f4f7f9; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 8px solid #1d4ed8;">

                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <img src="https://papers.wmc2026.org/logo-wmc.png" alt="WMC Logo" width="280"
                                style="display: block; margin: 0 auto 25px auto;">
                            <h1
                                style="color: #1e3a8a; font-size: 24px; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                                Registration Confirmed</h1>
                            <div style="width: 60px; height: 4px; background-color: #2563eb; margin: 15px auto;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 30px 40px; text-align: center; color: #4b5563; line-height: 1.6;">
                            <p style="font-size: 16px; margin: 0;">Dear
                                <strong>{{ $inscripcion->persona->nombres }}</strong>,
                            </p>
                            <p style="font-size: 15px; margin: 10px 0 0 0;">We are pleased to inform you that your
                                registration for <strong>{{ config('app.event_name') }}</strong> has been successfully
                                processed.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px;">
                            <div
                                style="background-color: #f8fafc; border-radius: 12px; padding: 25px; border: 1px solid #e2e8f0;">
                                <h3
                                    style="color: #1d4ed8; font-size: 14px; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                    Participant Details</h3>

                                <table width="100%" style="font-size: 14px; color: #334155;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b; width: 40%;">Full Name:</td>
                                        <td style="padding: 5px 0; font-weight: 600;">
                                            {{ $inscripcion->persona->nombres }}
                                            {{ $inscripcion->persona->apellido_paterno }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">
                                            {{ $inscripcion->persona->tipoDocumento->name_en }}:</td>
                                        <td style="padding: 5px 0; font-weight: 600;">
                                            {{ $inscripcion->persona->documento }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Category:</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #1e3a8a;">
                                            {{ $inscripcion->categoria_inscripcion->nombre_en }}</td>
                                    </tr>
                                    @if (count($dias_seleccionados) > 0)
                                        <tr>
                                            <td style="padding: 5px 0; color: #64748b;">Selected Days:</td>
                                            <td style="padding: 5px 0;">
                                                @foreach ($dias_seleccionados as $dia)
                                                    <span
                                                        style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-right: 4px;">{{ $dia }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 40px 40px 40px;">
                            <div style="background-color: #1e3a8a; border-radius: 12px; padding: 25px; color: #ffffff;">
                                <h3
                                    style="color: #93c5fd; font-size: 14px; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                                    Billing Summary</h3>

                                <table width="100%" style="font-size: 14px;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #bfdbfe;">Company / Tax Name:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            {{ $inscripcion->facturacion->nombre_facturador }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #bfdbfe;">Transaction ID:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">
                                            #{{ $pago->idtransaccion }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #bfdbfe;">Card:</td>
                                        <td style="padding: 5px 0; font-weight: 500; text-align: right;">••••
                                            {{ $digitos }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 20px 0 0 0; font-size: 18px; font-weight: 800;">TOTAL PAID:
                                        </td>
                                        <td
                                            style="padding: 20px 0 0 0; font-size: 22px; font-weight: 900; text-align: right; color: #60a5fa;">
                                            USD {{ number_format($inscripcion->facturacion->total, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="border-top: 1px solid #e2e8f0; padding-top: 25px; text-align: center;">

                                <tr>
                                    <td style="padding-bottom: 10px;">
                                        <p style="color: #64748b; font-weight: 500; margin: 0; font-size: 13px;">
                                            For accommodation with preferential rates:
                                        </p>
                                        <p style="margin: 5px 0 20px 0;">
                                            <a href="mailto:reservas@iimp.org.pe"
                                                style="color: #2563eb; text-decoration: none; font-weight: bold; font-size: 14px; margin-right: 15px;">
                                                <span style="font-size: 16px;">✉</span> reservas@iimp.org.pe
                                            </a>
                                            <a href="https://wa.me/51942797524" target="_blank"
                                                style="color: #16a34a; text-decoration: none; font-weight: bold; font-size: 14px;">
                                                <span style="font-size: 16px;">📱</span> +51 942 797 254 (Melisa Ramos)
                                            </a>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding-top: 10px; border-top: 1px dashed #f1f5f9;">
                                        <p
                                            style="color: #64748b; font-weight: 500; margin: 0; font-size: 13px; padding-top: 15px;">
                                            For any further inquiries, please contact us:
                                        </p>
                                        <p style="margin: 5px 0 0 0;">
                                            <a href="mailto:inscripciones.wmc@iimp.org.pe"
                                                style="color: #2563eb; text-decoration: none; font-weight: bold; font-size: 14px; margin-right: 15px;">
                                                <span style="font-size: 16px;">✉</span> inscripciones.wmc@iimp.org.pe
                                            </a>
                                            <a href="https://wa.me/51951294314" target="_blank"
                                                style="color: #16a34a; text-decoration: none; font-weight: bold; font-size: 14px;">
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
                            <p>Thank you for being part of <strong>{{ config('app.event_name') }}</strong>.</p>
                            <p style="margin-top: 15px; font-size: 11px; color: #cbd5e1;">This is an automated message.
                                Please do not reply to this email. If you have questions, contact our support team.</p>
                        </td>
                    </tr>
                </table>

                <table width="100%" max-width="600" style="max-width: 600px; margin-top: 20px;">
                    <tr>
                        <td
                            style="text-align: center; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                            &copy; {{ date('Y') }} {{ config('app.event_name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
