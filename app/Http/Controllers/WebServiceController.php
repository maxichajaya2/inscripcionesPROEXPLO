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
		$this->url_connection='http://200.37.185.5:8080';

		$this->VALID_IP = "200.37.185.4";
		$this->VALID_PASS = '$2y$10EoqWZuIEQ4vnwtm2IU3bruqmBD9yDiLrdIGNTHnSIRgAAatpBE9YK';
		$this->EVENT = "proexplo";
		$this->CURRENT_EVENT = "PROEXPLO 2025";
		$this->ID_EVENT = "3";
		$this->SIE_CODE_TIPO_EVENT = "1";
		$this->SIE_CODE_EVENT = "25";


        $this->urlPersonValidation = 'https://secure2.iimp.org:8443/KB_WEBASOCJavaEnvironment/rest/validarAsociado';
        $this->url_new_connection = "https://services.iimp.org.pe";

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

    public function wsPersona_create_update($persona){
        try {
            $url = "{$this->url_new_connection}/connection.php";

            $ws['ipAddress'] = config('app.valid_ip');
            $ws['accessKey'] = config('app.valid_pass');
            $ws['serviceKey'] = "ws";
            $ws['event'] = config('app.evento');
            $ws['id_event'] = config('app.id_evento');
            $ws['siecode_event'] = config('app.event_code');

            $data_ws = [
                'service'           => "persona_register_update",
                'id_tipo_documento' => $persona->tipoDocumento->sie_code,
                'numero_documento'  => $persona->documento ,
                'nombres'           => ($persona->nombres),
                'apellido_paterno'  => ($persona->apellido_paterno),
                'apellido_materno'  => ($persona->apellido_materno),
                'sexo'              => $persona->sexo,
                'fecha_nacimiento'  => $persona->fecha_nacimiento,
                'id_nacionalidad'   => $persona->id_nacionalidad,
                'correo'            => $persona->correo,
                'celular'           => $persona->celular,
                'direccion'         => ($persona->direccion->direccion),
                'empresa_siecode'   => '',
                'id_ocupacion'      => $persona->id_ocupacion,
                'pais'              => $persona->direccion->id_pais,
                'departamento'      => $persona->direccion->id_departamento ,
                'provincia'         => $persona->direccion->id_provincia,
                'distrito'          => $persona->direccion->id_distrito ,
                'ubigeo'            => '',
                'sie_code_persona'  => $persona->sie_code_persona,
            ];

            $ws['data'] = $data_ws;

            $response = $this->sendWS($url, json_encode($ws));

            if (isset($response->message) && (strpos($response->message, "Success") !== false) ) {
                return ['status' => true, 'sie_code' => $response->sie_code ];
            } else {
                return ['status' => false];
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return ['status' => false];
        }
    }

    private function validateService ($request) {
        if($request->ok()) {
            return true;
        } else {
            return false;
        }
    }

    public function sendWS($url , $data, $header = null,$headername = null){

            if (!is_null($header)) {

                if(is_null($headername)){
                    $headername = "Authorization";
                }

                $content = array(
                    $headername .': '.$header,
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
            curl_close($ch);

            $decoded_response = json_decode($response);

            if(json_last_error() === JSON_ERROR_NONE){
                return $decoded_response;
            }else{
                return $response;
            }

	}

    /*public function sendWS($url,$data, $head = null)
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
	}*/
}
