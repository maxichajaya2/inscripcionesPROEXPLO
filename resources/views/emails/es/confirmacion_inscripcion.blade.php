<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Inscripción </title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e0e0e0; padding: 30px;">
        <h2 style="color: #1a202c;">{{ config('app.event_name') }} - Confirmación de Inscripción</h2>
    <!--    <p>Estimado(a) <strong>{{ $persona->nombres }} {{ $persona->apellido_paterno }} {{ $persona->apellido_materno }}</strong>,</p> -->
        <p>Nos complace informarte que tu inscripción como <strong> {{ $inscripcion->categoria_inscripcion->nombre_es }}</strong> ha sido </strong>confirmada exitosamente
           para el evento <strong>{{ config('app.event_name') }}</strong>.</p>

        <h3 style="color: #2d3748; margin-top: 30px;">Datos del Participante</h3>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">Nombre completo:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">{{ $inscripcion->persona->nombres }} {{ $inscripcion->persona->apellido_paterno }} {{ $inscripcion->persona->apellido_materno }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">{{ $inscripcion->persona->documento }}
                    Documento:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">{{ $inscripcion->persona->documento }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">Correo:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">{{ $persona->correo }}</td>
            </tr>
        </table>

        <h3 style="color: #2d3748;">Datos Generales</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">



        </table>

        <p style="margin-top: 30px;">Si tienes alguna consulta, puedes responder a este correo o comunicarte con nosotros.</p>
        <p style="color: #4a5568;">¡Gracias por participar en {{ config('app.event_name') }}!</p>
        <hr style="margin: 30px 0;">
        <p style="font-size: 12px; color: #a0aec0;">Este correo es informativo y ha sido generado automáticamente. Por favor, no responder a este mensaje.</p>
    </div>
</body>
</html>
