<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Http\Controllers\WebServiceController;
use stdClass;

class DocumentApiController extends Controller
{
    protected $urlApi;

    public function __construct()
    {
        $this->urlApi = 'https://api.apis.net.pe/v2';
    }

        protected function token(){
        return 'apis-token-13383.Aph50ddFaV03b9sZaRprJo5ZBpMz0yC4';
    }

    public function getData($type, $data){
        if($type == "dni"){
            $urlApi = $this->urlApi .'/reniec/'.$type.'?numero='.$data;
        }

        if($type == "ruc"){
            $urlApi = $this->urlApi .'/sunat/ruc/full?numero='.$data;
        }


        $request = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->token(),
            'Accept' => 'application/json'
        ])->get($urlApi);

        if($request->ok()){
            $request =   json_decode($request);
            if(isset($request->numeroDocumento)){
                $request->success = true;
            }else{
                $request->success = false;
            }

            $json = $request;
        } else {
            $json = ['status' => false];
        }
        return $json;
    }

    public function getPersonData(Request $request) {

        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);
        $persona = Persona::where('id_tipo_documento',$request->id_tipo_documento)->where('documento',$request->numero_documento)->first();

        if($persona){
            $persona->pais = $persona->direccion->id_pais;
            $persona->departamento  = $persona->direccion->id_departamento;
            $persona->provincia  = $persona->direccion->id_provincia;
            $persona->distrito  = $persona->direccion->id_distrito;
            $persona->nacionalidad  = $persona->id_nacionalidad;
            $persona->direccionPersona  = $persona->direccion->direccion;
            $persona->cargo = $persona->ocupacion->name;
            $persona->ocupacion = $persona->ocupacion->name;

        }else{
            $persona = new \stdClass();
        }


        if($request->id_tipo_documento == 1 ){
            if($request->id_tipo_documento == 1 ){
                $api_persona = $this->getData('dni', $request->numero_documento);

                $persona->nombres = $api_persona->nombres;
                $persona->apellido_paterno = $api_persona->apellidoPaterno;
                $persona->apellido_materno = $api_persona->apellidoMaterno;

            }
        }else{
            $persona->id_tipo_documento = $request->id_tipo_documento;
            $persona->documento = $request->numero_documento;
        }

        $persona->es_socio = app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento);

        return json_encode(['persona' => $persona]);

    }

    public function validatePersonSoc(Request $request) {

        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);

        $valid = app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento);

        $persona = [
            'id_tipo_documento' => $request->id_tipo_documento,
            'numero_documento' => $request->numero_documento,
            'es_socio' => app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento)
        ];

        return json_encode(['persona' => $persona]);

    }
}
