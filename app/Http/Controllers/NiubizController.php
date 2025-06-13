<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Helpers\HtmlHelper;
use Illuminate\Support\Facades\Http;

class NiubizController extends Controller
{
    public function __construct()
    {
        $this->url_timeout="";
		$this->url_callback="";

		$this->url_api = "https://services.iimp.org.pe/";

    }

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

}
