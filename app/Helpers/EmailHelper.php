<?php

namespace App\Helpers;

use App\Models\Empresa;
use App\Models\TipoDocumento;
use App\Models\TipoDocumentoPago;
use App\Mail\MailContrato;
use Dompdf\Dompdf;
use Mail;


class EmailHelper
{

    function __construct()
	{
	}

    public static function emailContrato($facturacion,$contrato,$contactos,$productos){

        $empresa = Empresa::with('direccion')->find(auth()->user()->id_empresa);
        $data = [];
        try {

                $data['titulo'] = config('app.event_name')." - Convención Minera";
                $data['tipos_documentos_pago'] = TipoDocumentoPago::where('isactive',true)->get();
                $data['empresa']= $empresa;
                $data['facturacion'] = $facturacion;
                $data['contrato'] = $contrato;
                $data['productos'] = $productos;
                $data['contactos'] = [];

                // ordenar contactos 1 Responsable de Firma de Contrato 2 Responsable Extemin 3 Responsable de Pagos
                $i = 0; $repeat = 0;
                do{
                    $single_contacto = $contactos[$i];
                    if($contactos[$i]->tipoContacto->name == "Responsable de Firma de Contrato"){
                        $data['contactos'][0] = $single_contacto;
                    }
                    if($contactos[$i]->tipoContacto->name == "Responsable Extemin"){
                        $data['contactos'][1] = $single_contacto;
                    }
                    if($contactos[$i]->tipoContacto->name == "Responsable de Pagos"){
                        $data['contactos'][2] = $single_contacto;
                    }
                    $i++;
                    if($i == sizeof($contactos)){
                        $i = 0;
                        $repeat++;
                    }
                    if($repeat == 10){
                        throw new \Exception("Contacts types error");
                    }
                }while( (sizeof($data['contactos'])<3 ) || $repeat == 10);

                ksort($data['contactos']);

                try{
                    if(\App::environment('local')){
                        Mail::mailer('smtp')
                        ->to($empresa->correo)
                        ->send(new MailContrato($data));
                    }else{
                        Mail::mailer('smtp')
                        ->to($empresa->correo)
                        ->cc($data['contactos'][1]->persona->correo)
                        ->send(new MailContrato($data));
                    }
                }catch (\Exception $e){
                    return false;
                }

                return true;

		} catch (\Exception $e) {
			return false;
		}

		return true;

    }
}
