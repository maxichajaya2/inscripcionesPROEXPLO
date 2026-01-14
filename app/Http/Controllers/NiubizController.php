<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Helpers\HtmlHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailInscripcion;
use App\Models\Inscripcion;
use App\Models\Cuota;
use Inertia\Inertia;

class NiubizController extends Controller
{
    public function __construct()
    {
        $this->url_timeout = "";
        $this->url_callback = "";

        $this->url_api = "https://services.iimp.org.pe/";
    }

      /***************  PRODUCCION **************** */
    /** ==================================== */
     public function filterResponse($data){
		$info=[];
		$data = json_decode($data);
                if (isset($data->errorCode)) {
                  $info['errorcode']=$data->errorCode;
                  $info['ACTION_CODE']=$data->data->ACTION_CODE;
                  $info['ACTION_DESCRIPTION']=$data->data->ACTION_DESCRIPTION;
                }else{
					if(isset($data->dataMap->STATUS)){
						if($data->dataMap->STATUS === "Authorized"){
							$info['transactionDate'] = $data->dataMap->TRANSACTION_DATE;
							$info['transactionDate']=str_split($info['transactionDate'],2);
							for ($c=2; $c >=0; $c--) {
									$date[$c]=$info['transactionDate'][$c];
									}
							for ($c=3; $c < 6; $c++) {
									$time[$c]=$info['transactionDate'][$c];
									}
							$info['date']=implode("/", $date);
							$info['time']=implode(":", $time);
							$info['transactionId']=$data->order->transactionId;
							$info['CARD']=$data->dataMap->CARD;
							$info['BRAND']=$data->dataMap->BRAND;
						}
					}
                }

    	return $info;
    }

    public function getNumOrder(){
		$url = $this->url_api."index.php";

		$niubiz_data['ipAddress'] = config('app.valid_ip');
		$niubiz_data['accessKey'] = config('app.valid_pass');
		$niubiz_data['serviceKey'] = "numero_orden";
		$niubiz_data['event'] = config('app.evento');
		$niubiz_data['id_event'] = config('app.id_evento');
		$niubiz_data['siecode_event'] = config('app.event_code');

		$data['codigo_tipo_pago'] = "niubiz_tarjeta";
		$niubiz_data['data']=$data;

		return app(\App\Http\Controllers\WebServiceController::class)->sendWS($url,json_encode($niubiz_data) );
	}

    public function authorization($key,$amount,$transactionToken,$purchaseNumber){

		$url = $this->url_api."niubiz.php";

		$niubiz_data['ipAddress'] = config('app.valid_ip');
		$niubiz_data['accessKey'] = config('app.valid_pass');
		$niubiz_data['serviceKey'] = "niubiz_tarjeta";
		$niubiz_data['event'] = config('app.evento');
		$niubiz_data['id_event'] = config('app.id_evento');
		$niubiz_data['siecode_event'] = config('app.event_code');

		$data['key'] = $key;
		$data['url_timeout'] = "";
		$data['url_callback'] = "";
		$data['amount'] = $amount;
		$data['token'] = $transactionToken;
		$data['purchasenumber'] = $purchaseNumber;
		$data['process'] = "get_authorization";
		$niubiz_data['data']=$data;

		return app(\App\Http\Controllers\WebServiceController::class)->sendWS($url,json_encode($niubiz_data) );
    }

    public function getForm($persona, $inscripcion , $facturacion , $url_timeout, $url_base){

        $url = $this->url_api."niubiz.php";

        $numero_orden = $this->getNumOrder();

        $niubiz_data['ipAddress'] = config('app.valid_ip');
		$niubiz_data['accessKey'] = config('app.valid_pass');
		$niubiz_data['serviceKey'] = "niubiz_tarjeta";
		$niubiz_data['event'] = config('app.evento');
		$niubiz_data['id_event'] = config('app.id_evento');
		$niubiz_data['siecode_event'] = config('app.event_code');

        $data = new \stdClass();

        $data->url_timeout = $url_timeout;
		$data->url_callback = $url_base.'/niubiz/'.$facturacion->id.'/'.$numero_orden->numero_orden ;
        $data->process = "get_form";
		$data->logo = config('app.url_logo_niubiz');
		$data->color_code = "#004F59";

        $nombre = explode(" ", $persona->nombres);
        $nombre = trim($nombre[0]);

        $data->nombre = $nombre;
        $data->apellido = $persona->apellido_paterno;
        $data->email = $persona->correo;
		$data->numerodocumento = $persona->documento;
		$data->idpersona = $persona->id;
		$data->amount = $facturacion->total;//
		$data->telefono = $persona->celular;
		$data->items_number = 1;
		$data->numero_orden = $numero_orden->numero_orden;
        $data->raw_data = true;

		$niubiz_data['data']= $data;

        return app(\App\Http\Controllers\WebServiceController::class)->sendWS($url, json_encode($niubiz_data));

    }

     /***************  LOCAL **************** */
    /** ==================================== */
    // private function config()
    // {
    //     return (object) config('services.niubiz');
    // }

    // public function filterResponse($data)
    // {
    //     $info = [];
    //     // Si $data es un objeto de Laravel (JsonResponse), extraemos los datos
    //     if ($data instanceof \Illuminate\Http\JsonResponse) {
    //         $data = $data->getData();
    //     }

    //     // Si es un string, lo decodificamos, si no, lo usamos como objeto
    //     if (is_string($data)) {
    //         $data = json_decode($data);
    //     } else {
    //         $data = (object) $data;
    //     }

    //     if (isset($data->errorCode)) {
    //         $info['errorcode'] = $data->errorCode;
    //         $info['ACTION_DESCRIPTION'] = $data->data->ACTION_DESCRIPTION ?? 'Error';
    //     } else {
    //         // Extraer datos del mapa de datos de Niubiz
    //         $dataMap = (object) ($data->dataMap ?? []);

    //         if (isset($dataMap->STATUS) && $dataMap->STATUS === "Authorized") {
    //             $info['transactionId'] = $data->order['transactionId'] ?? ($data->order->transactionId ?? '');
    //             $info['CARD'] = $dataMap->CARD ?? '';
    //             $info['BRAND'] = $dataMap->BRAND ?? '';
    //             $info['ACTION_DESCRIPTION'] = $dataMap->ACTION_DESCRIPTION ?? 'Aprobado';

    //             // Si Niubiz no manda fecha, usamos la del sistema para que no falle la BD
    //             $info['date'] = date('d/m/y');
    //             $info['time'] = date('H:i:s');
    //         }
    //     }

    //     return $info;
    // }

    // public function getNumOrder()
    // {

    //     $url = $this->url_api . "index.php";

    //     $niubiz_data['ipAddress'] = config('app.valid_ip');
    //     $niubiz_data['accessKey'] = config('app.valid_pass');
    //     $niubiz_data['serviceKey'] = "numero_orden";
    //     $niubiz_data['event'] = config('app.evento');
    //     $niubiz_data['id_event'] = config('app.id_evento');
    //     $niubiz_data['siecode_event'] = config('app.event_code');

    //     $data['codigo_tipo_pago'] = "niubiz_tarjeta";
    //     $niubiz_data['data'] = $data;

    //     return app(\App\Http\Controllers\WebServiceController::class)->sendWS($url, json_encode($niubiz_data));
    // }

    // public function authorization($sessionKey, $amount, $transactionToken, $purchaseNumber)
    // {
    //     $conf = $this->config();

    //     // 1. Formatear monto (Vital para Niubiz)
    //     $amount = number_format($amount, 2, '.', '');

    //     try {
    //         // 2. Token de Seguridad
    //         $auth = base64_encode($conf->user . ':' . $conf->password);
    //         $accessToken = Http::withoutVerifying()
    //             ->withHeaders(['Authorization' => 'Basic ' . $auth])
    //             ->post($conf->url_api . '/api.security/v1/security')
    //             ->body();

    //         // 3. Autorización (El cobro real)
    //         $response = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'Authorization' => $accessToken,
    //                 'Content-Type' => 'application/json'
    //             ])
    //             ->post($conf->url_api . "/api.authorization/v3/authorization/ecommerce/{$conf->merchant_id}", [
    //                 'channel' => 'web',
    //                 'captureType' => 'manual',
    //                 'countable' => true,
    //                 'order' => [
    //                     'tokenId' => $transactionToken,
    //                     'purchaseNumber' => $purchaseNumber,
    //                     'amount' => $amount,
    //                     'currency' => 'USD'
    //                 ]
    //             ]);


    //         //     dd([
    //         //      'STATUS' => $response->status(), // Código HTTP (200, 400, 500)
    //         //      'BODY_RAW' => $response->body(), // Lo que envió Niubiz en texto crudo
    //         //      'JSON' => $response->json(),     // Lo que Laravel entendió
    //         //      'TOKEN_USADO' => $accessToken,
    //         //      'PARAMS_ENVIADOS' => [
    //         //          'tokenId' => $transactionToken,
    //         //          'amount' => $amount
    //         //      ]
    //         //  ]);
    //         // 4. ¡AQUÍ ESTÁ LA CLAVE!
    //         // NO creamos arreglos nuevos como ['status' => true], eso borraba la data.
    //         // return response()->json($response->json());
    //         return $response->json();
    //     } catch (\Exception $e) {
    //         // Si falla la conexión, devolvemos un error estructurado
    //         return response()->json([
    //             'errorCode' => '500',
    //             'errorMessage' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function getForm($persona, $inscripcion, $facturacion)
    // {
    //     // 1. OBTENER CONFIGURACIÓN DEL .ENV (Lo que pusiste del curso)
    //     $conf = $this->config();

    //     $amount = number_format($facturacion->total, 2, '.', '');
    //     $purchaseNumber = (string) $inscripcion->id; // Usamos ID inscripción como orden

    //     try {
    //         // --------------------------------------------------------------------------
    //         // PASO A: OBTENER TOKEN DE SEGURIDAD (SECURITY)
    //         // --------------------------------------------------------------------------
    //         $auth = base64_encode($conf->user . ':' . $conf->password);
    //         $urlSecurity = $conf->url_api . '/api.security/v1/security';

    //         // Usamos withoutVerifying() para saltar el bloqueo de tu red/antivirus
    //         $responseSecurity = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'Authorization' => 'Basic ' . $auth,
    //             ])
    //             ->post($urlSecurity);

    //         if ($responseSecurity->failed()) {
    //             throw new \Exception('Error Security: ' . $responseSecurity->body());
    //         }

    //         $accessToken = $responseSecurity->body();

    //         // --------------------------------------------------------------------------
    //         // PASO B: CREAR LA SESIÓN DE PAGO (SESSION)
    //         // --------------------------------------------------------------------------
    //         $urlSession = $conf->url_api . "/api.ecommerce/v2/ecommerce/token/session/{$conf->merchant_id}";

    //         $responseSession = Http::withoutVerifying()
    //             ->withHeaders([
    //                 'Authorization' => $accessToken,
    //                 'Content-Type' => 'application/json'
    //             ])
    //             ->post($urlSession, [
    //                 'channel' => 'web',
    //                 'amount' => $amount,
    //                 'antifraud' => [
    //                     'clientIp' => request()->ip(),
    //                     'merchantDefineData' => [
    //                         'MDD4' => $persona->correo,
    //                         'MDD32' => $persona->documento,
    //                         'MDD75' => 'Invitado',
    //                         'MDD77' => 1
    //                     ],
    //                 ],
    //             ]);

    //         if ($responseSession->failed()) {
    //             throw new \Exception('Error Session: ' . $responseSession->body());
    //         }

    //         $sessionToken = $responseSession->json()['sessionKey'];

    //         // --------------------------------------------------------------------------
    //         // PASO C: PREPARAR DATOS PARA EL FRONTEND (IGUAL QUE TU CÓDIGO COMENTADO)
    //         // --------------------------------------------------------------------------
    //         // Aquí construimos el objeto que tu Vue necesita leer
    //         $dataParaFrontend = [
    //             'action'         => $conf->url_js,         // La URL del JS de Niubiz
    //             'merchantId'     => $conf->merchant_id,
    //             'amount'         => $amount,
    //             'purchaseNumber' => $purchaseNumber,
    //             'channel'        => 'web',
    //             'quotaId'        => $inscripcion->id_facturacion,
    //             'name'           => $persona->nombres,
    //             'lastname'       => $persona->apellido_paterno,
    //             'email'          => $persona->correo,
    //             'token'          => $sessionToken          // EL TOKEN DE SESIÓN
    //         ];

    //         // dd( $dataParaFrontend );
    //         // Retornamos la estructura exacta: 'k' status y 'frm' data encriptada
    //         return (object) [
    //             'k' => 'OK',
    //             'frm' => base64_encode(json_encode($dataParaFrontend))
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error("Niubiz Error: " . $e->getMessage());
    //         // Si falla, lanzamos la excepción para que el Frontend sepa que hubo error
    //         throw $e;
    //     }
    // }


}
