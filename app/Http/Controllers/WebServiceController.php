<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Contacto;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Helpers\HtmlHelper;
use App\Models\PorcentajeCuotas;
use Illuminate\Support\Facades\Http;

class WebServiceController extends Controller
{
    public function __construct()
    {
        $this->url_enviroment = 'KBServiciosIIMPJavaEnvironment';
		$this->url_connection='http://200.37.185.5:8080';//https://secure2.iimp.org:8443'http://200.37.185.5:8080/KBServiciosIIMPJavaEnvironment/rest/WSInscripcionInternet
		$this->VALID_IP = "200.37.185.4";
		$this->VALID_PASS = '$2y$10EoqWZuIEQ4vnwtm2IU3bruqmBD9yDiLrdIGNTHnSIRgAAatpBE9YK';
		$this->EVENT = "proexplo";
		$this->CURRENT_EVENT = "PROEXPLO 2025";
		$this->ID_EVENT = "3";
		$this->SIE_CODE_TIPO_EVENT = "1";
		$this->SIE_CODE_EVENT = "25";
		$this->url_new_connection = "https://services.iimp.org.pe";

        $this->urlPersonValidation = 'https://secure2.iimp.org:8443/KB_WEBASOCJavaEnvironment/rest/validarAsociado';

    }



    public function validatePersonMember($id_sie_documento, $numero_documento){

        $data_ws = [
            "tipoDocumento"=> $id_sie_documento,
            "numeroDocumento"=> $numero_documento
        ];

        $request = Http::post($this->urlPersonValidation , $data_ws);

        if($request->ok()){
            $respuesta =   json_decode($request);
            if(isset($respuesta->codeMessage)){
                if($respuesta->codeMessage) return true;
            }else{
                return  false;
            }
        }else {
            return false;
        }

        return false;

    }

    private function validateService ($request) {
        if($request->ok()) {
            return true;
        } else {
            return false;
        }
    }


    public function sendWS($url,$data, $head = null)
	{
		if (!is_null($head)) {
			$content = array(
				'Authorization: '.$head,
				'Content-Type: application/json',
				'Content-Length: '.strlen($data)
			);
		}else{
			$content = array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($data)
			);
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $content );
		$response = curl_exec($ch);
		$response = json_decode($response);
		curl_close($ch);

		return $response;
	}
}
