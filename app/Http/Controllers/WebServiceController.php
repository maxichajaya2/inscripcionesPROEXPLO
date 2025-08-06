<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Contacto;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Helpers\HtmlHelper;
use App\Models\PorcentajeCuotas;
use App\Models\TipoPago;
use Illuminate\Support\Facades\Http;

class WebServiceController extends Controller
{
    public function __construct()
    {
        $this->url_enviroment = 'KBServiciosIIMPJavaEnvironment';
		$this->url_connection='http://200.37.185.5:8080';
        $this->now = Carbon::now()->format('Y-m-d');

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

    public function wsInscripcion_create_update( $facturacion, $persona, $inscripcion , $niubiz){
		try{
			$url= "{$this->url_new_connection}/connection.php";

            $tipo_pago = TipoPago::find($facturacion->id_tipo_pago);
            $inscripcion->categoria_inscripcion->precio_disponible = $inscripcion->categoria_inscripcion->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();

            if( ($persona->id_ocupacion == 2795) && ( strlen($inscripcion->texto_cargo)) > 0 ){ //indice ocupacion no especificada o no se encuentra en el listado
                $cargo = $inscripcion->texto_cargo;
            }else {
                $cargo = $persona->ocupacion->name;
            }

            $data_ws =[];

            if(str_contains($inscripcion->categoria_inscripcion->nombre_es, 'DIA')){
                $data_ws['lun'] = 0;
                $data_ws['mar'] = 0;
                $data_ws['mie'] = 0;
                $data_ws['jue'] = 0;
                $data_ws['vie'] = 0;
                $data_ws['ficha_condicion'] = '';

                $dias = json_decode($inscripcion->dias, true);

                foreach($dias as $key => $dia){
                    if($dia){
                        $data_ws[$key] = 1;
                        if($data_ws['ficha_condicion'] == ''){
                            $data_ws['ficha_condicion'] = strtoupper( substr($key ,0,2) );
                        }
                    }
                }

            }else{
                $data_ws['lun'] = 1;
                $data_ws['mar'] = 1;
                $data_ws['mie'] = 1;
                $data_ws['jue'] = 1;
                $data_ws['vie'] = 1;
                $data_ws['ficha_condicion'] = $inscripcion->categoria_inscripcion->condicion;
            }

            $ws['ipAddress'] = config('app.valid_ip');
            $ws['accessKey'] = config('app.valid_pass');
            $ws['serviceKey'] = "ws";
            $ws['event'] = config('app.evento');
            $ws['id_event'] = config('app.id_evento');
            $ws['siecode_event'] = config('app.event_code');

			$data_ws['service'] = "inscripcion_register_update";
			$data_ws['inscrito_idtipodocumento'] = $persona->tipoDocumento->sie_code;
			$data_ws['inscrito_numerodocumento'] = $persona->documento;

            $data_ws['inscrito_empresa'] = $facturacion->observacion;
			$data_ws['inscrito_cargo'] = $cargo;
			$data_ws['auth_datos'] = $inscripcion->autorizacion_datos > 0 ? 1 : 0;
			$data_ws['pago_estado'] = "PAGADO";
			$data_ws['pago_tipo'] = $tipo_pago->siecode;
			$data_ws['pago_tarjeta'] = $niubiz->card_num;
			$data_ws['pago_orden'] = $niubiz->num_orden;
			$data_ws['pago_transaccion'] = $niubiz->idtransaccion;
            $data_ws['facturacion_razon_social'] = $facturacion->nombre_facturador;
            $data_ws['facturacion_siecode_tipo_documento'] = $facturacion->tipoDocumentoFacturador->sie_code;
			$data_ws['facturacion_numero_documento'] = $facturacion->numero_doc_facturador;
			$data_ws['facturacion_tipo_documentopago'] = $facturacion->tipoDocumentoPago->siecode;
			$data_ws['facturacion_persona'] = $facturacion->responsable_facturador;
			$data_ws['facturacion_telefono'] = $persona->celular;
			$data_ws['facturacion_email'] = $persona->correo;
			$data_ws['facturacion_direccion'] = $facturacion->direccion_facturador;
            $data_ws['facturacion_importe'] = (float)$facturacion->total;
            $data_ws['ficha_tipo'] = 2; //inscripciones individuales
			$data_ws['ficha_control'] = $inscripcion->categoria_inscripcion->control;
			$data_ws['ficha_categoria'] = $inscripcion->categoria_inscripcion->categoria;

            $data_ws['simbolo_moneda'] = $inscripcion->categoria_inscripcion->precio_disponible->moneda->simbolo;
			$data_ws['evento_tipo'] = config('app.event_type');
			$data_ws['evento_codigo'] = config('app.event_code') ;
			$data_ws['ficha_id'] = $inscripcion->id;

			$ws['data'] = $data_ws;

			$response = $this->sendWS($url, json_encode($ws));

			if( strpos( $response->Message, "Success") !== false ){
				return ['status' => true ];
			}else{
				return ['status' => false ];
			}

		}catch (RequestException $e) {
            return ['status' => false ];
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

}
