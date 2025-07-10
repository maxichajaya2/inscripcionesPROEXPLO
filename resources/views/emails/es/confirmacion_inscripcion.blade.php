@php
    $dias_nombres = [
            'lun' => 'Lunes',
            'mar' => 'Martes',
            'mie' => 'Miercoles',
            'jue' => 'Jueves',
            'vie' => 'Viernes'
            ];
    $dias_seleccionados = [];

    if(str_contains($inscripcion->categoria_inscripcion->nombre_es, 'DIA')){

        $dias_inscripcion = json_decode($inscripcion->dias, true);

        foreach($dias_inscripcion as $key => $dia){
            if($dia){
                $dias_seleccionados[] = $dias_nombres[$key];
            }
        }
    }

    $digitos = substr( $pago->card_num ,-8);
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Inscripción </title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #e0e0e0; padding: 30px;">
        <div>
            <div  style ="display: flex; justify-content: space-around; text-align:center;">
                <img src="https://perumin.com/perumin37/public/uploads/shares/Home/LOGO_PERUMIN_37.png" alt="" width="340px" style= "max-width: 340px; margin: auto;">
            </div>
            <div  style ="display: flex; justify-content: space-around; text-align:center;">
                <h2 style="color: #1a202c; margin: auto;">{{ config('app.event_name') }} - Confirmación de Inscripción</h2>
            </div>
        </div>
        <p>Nos complace informarte que tu inscripción como <strong> {{ $inscripcion->categoria_inscripcion->nombre_es }}</strong> ha sido </strong>confirmada exitosamente
           para el evento <strong>{{ config('app.event_name') }}</strong>.</p>

        <h3 style="color: #2d3748; margin-top: 30px;">DATOS DEL PARTICIPANTE</h3>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Nombre completo:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->nombres }} {{ $inscripcion->persona->apellido_paterno }} {{ $inscripcion->persona->apellido_materno }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->tipoDocumento->name_es }}:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->documento }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Email: </td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->correo }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Direccion:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->direccion->direccion }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Telefono:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->celular }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Empresa:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->observacion }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Cargo:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->persona->ocupacion->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Autorización para el tratamiento de Datos Personales:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->autorizacion_datos ? "Si" : "No" }}</td>
            </tr>
        </table>

        <h3 style="color: #2d3748;">INSCRIPCIÓN</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Categoria:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->categoria_inscripcion->nombre_es }}</td>
            </tr>
            @if(sizeof($dias_seleccionados) > 0)
                <tr>
                    <td style = "padding: 8px; border: 1px solid #e2e8f0; width:270px; margin: auto; text-align: center;" colspan="2">

                            @foreach($dias_seleccionados as $dia)
                                <input type="checkbox" checked style = "padding-right: 5px;" id ='{{ $dia }}'>
                                <label for="" style = "padding-right: 10px;">{{ $dia }}</label>
                            @endforeach
                    </td>
                </tr>
            @endif
        </table>

        <h3 style="color: #2d3748;">FACTURACIÓN</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Factura a nombre de:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->nombre_facturador }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->tipoDocumentoFacturador->name_es }}:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->numero_doc_facturador }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Dirección:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->direccion_facturador }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Persona de Contacto:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->responsable_facturador }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Forma de pago:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->tipoPago->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Tipo de comprobante de Pago:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $inscripcion->facturacion->tipoDocumentoPago->nombre }}</td>
            </tr>

            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">N° TRANSACCION::</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $pago->idtransaccion }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">Ulitmos Dígitos de Tarjeta:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">{{ $digitos }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">TOTAL:</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0; width:270px;">USD {{ $inscripcion->facturacion->total }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Si tienes alguna consulta, puedes responder a este correo o comunicarte con nosotros.</p>
        <p style="color: #4a5568;">¡Gracias por participar en {{ config('app.event_name') }}!</p>
        <hr style="margin: 30px 0;">
        <p style="font-size: 12px; color: #a0aec0;">Este correo es informativo y ha sido generado automáticamente. Por favor, no responder a este mensaje.</p>
    </div>
</body>
</html>
