<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Http\Controllers\WebServiceController;
use Carbon\Carbon;
use stdClass;

class DocumentApiController extends Controller
{
    protected $urlApi;

    public function __construct()
    {
        $this->urlApi = 'https://api.apis.net.pe/v2';
        $this->now = Carbon::now()->format('Y-m-d');
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
                $response = [ 'persona' => $request ,'status' => true];
            }else{
                $response = ['status' => false];
            }

        } else {
            $response = ['status' => false];
        }
        return $response;
    }

    public function getPersonData(Request $request) {

        if ( !str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null ) ){
            abort(403, 'Unauthorized POST request.');
            exit;
        }

        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);
        $persona = Persona::where('id_tipo_documento',$request->id_tipo_documento)->where('documento',$request->numero_documento)->first();
        $status = true;

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
            $persona->id_tipo_documento = $request->id_tipo_documento;
            $persona->documento = $request->numero_documento;
            $persona->pais = 0;
            $persona->departamento  = 0;
            $persona->provincia  = 0;
            $persona->distrito  = 0;
            $persona->nacionalidad  = 0;
            $persona->direccionPersona  = "";
            $persona->cargo = "";
            $persona->ocupacion = "";
            $persona->celular = "";
            $persona->correo = "";
            $persona->sexo = 0;
            $persona->nombres = "";
            $persona->apellido_paterno = "";
            $persona->apellido_materno = "";
            $persona->fecha_nacimiento = $this->now;
        }

        if($request->id_tipo_documento == 1 ){

                $api_persona = $this->getData('dni', $request->numero_documento);

                if($api_persona['status']){
                    $persona->nombres = $api_persona['persona']->nombres;
                    $persona->apellido_paterno = $api_persona['persona']->apellidoPaterno;
                    $persona->apellido_materno = $api_persona['persona']->apellidoMaterno;
                }else{
                    $status = false;
                }

        }

        $persona->es_socio = app(\App\Http\Controllers\WebServiceController::class)->validatePersonMember($tipo_documento->sie_code, $request->numero_documento);

        return json_encode(['persona' => $persona , 'status' => $status ]);

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
