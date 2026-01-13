<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
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
            $tipo_respuesta = 'persona';
            $urlApi = $this->urlApi .'/reniec/'.$type.'?numero='.$data;
        }

        if($type == "ruc"){
            $tipo_respuesta = 'empresa';
            $urlApi = $this->urlApi .'/sunat/ruc/full?numero='.$data;
        }


        $request = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->token(),
            'Accept' => 'application/json'
        ])->get($urlApi);

        if($request->ok()){
            $request =   json_decode($request);
            if(isset($request->numeroDocumento)){
                $response = [ $tipo_respuesta => $request ,'status' => true];
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

        /***  1. Validamos que se encuentre registrado ***/
        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);
        $persona = Persona::where('id_tipo_documento',$request->id_tipo_documento)
                    ->where('documento',$request->numero_documento)
                    ->first();
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

        $persona->es_socio = app(\App\Http\Controllers\WebServiceController::class)
        ->validatePersonMember($tipo_documento->sie_code, $request->numero_documento);

        return json_encode(['persona' => $persona , 'status' => $status ]);

    }

    public function getEmpresaData(Request $request)
    {
        $direccion = "";
        $status = true;

        if($request->tipo_doc == 1){
            $empresa = Persona::where('id_tipo_documento', $request->tipo_doc)->where('documento', $request->documento)->first();
        }else{
            $empresa = Empresa::where('id_tipo_documento', $request->tipo_doc)->where('documento', $request->documento)->first();
        }

        if($empresa){

            if( is_null($empresa->direccion) ){
                return response()->json([
                    'message'=> "Empresa no cuenta con direccion registrada",
                ]);
            }

            $empresa->pais = $empresa->direccion->id_pais;
            $empresa->departamento  = intval($empresa->direccion->id_departamento) > 0 ? $empresa->direccion->id_departamento : 0;
            $empresa->provincia  = intval($empresa->direccion->id_provincia) > 0 ? $empresa->direccion->id_provincia : 0;
            $empresa->distrito  = intval($empresa->direccion->id_distrito) > 0 ? $empresa->direccion->id_distrito : 0;

            if($empresa instanceof Persona) {
                $empresa->nombre = trim($empresa->nombres ." ". $empresa->apellido_paterno ." ". $empresa->apellido_materno );
                $empresa->telefono = $empresa->celular;
            }

            $departamento = Departamento::where('id_pais',intval($empresa->pais))->where('id_departamento',intval($empresa->departamento))->first();
            $departamento = ($departamento) ? $departamento->name : '';

            $provincia = Provincia::where('id_pais',intval($empresa->pais))->where('id_departamento',intval($empresa->departamento))
                        ->where('id_provincia',intval($empresa->provincia))->first();
            $provincia = ($provincia) ? $provincia->name : '';

            $distrito = Distrito::where('id_pais',intval($empresa->pais))->where('id_departamento',intval($empresa->departamento))
                        ->where('id_provincia',intval($empresa->provincia))->where('id_distrito',intval($empresa->distrito))->first();
            $distrito = ($distrito) ? $distrito->name : '';

            $direccion = trim($empresa->direccion->direccion." ". $departamento ." - ". $provincia ." - ". $distrito);

        }else{
            $empresa = new \stdClass();
            $empresa->id_tipo_documento = $request->id_tipo_documento;
            $empresa->documento = $request->numero_documento;
            $empresa->pais = 0;
            $empresa->departamento  = 0;
            $empresa->provincia  = 0;
            $empresa->distrito  = 0;
            $empresa->direccionEmpresa  = "";
            $empresa->telefono = "";
            $empresa->correo = "";
            $empresa->nombre = "";
            $empresa->nombre_comercial = "";
            $empresa->web = "";

            $status = false;
        }

        if($request->tipo_doc == 1 ){ //dni

                $api_empresa = $this->getData('dni', $request->documento);

                if($api_empresa['status']){
                    $api_empresa = $api_empresa['persona'];
                    $empresa->nombre = trim($api_empresa->nombres ." ". $api_empresa->apellidoPaterno ." ". $api_empresa->apellidoMaterno );

                }else{
                    $status = false;
                }
        }

        if($request->tipo_doc == 2 ){ //ruc

                $api_empresa = $this->getData('ruc', $request->documento);

                if($api_empresa['status']){
                    $api_empresa = $api_empresa['empresa'];

                    $empresa->nombre = $api_empresa->razonSocial;
                    $direccion = trim($api_empresa->direccion." ". $api_empresa->departamento ." - ". $api_empresa->provincia ." - ". $api_empresa->distrito);

                }else{
                    $status = false;
                }

        }

        $empresa->direccionEmpresa = $direccion;

        return json_encode(['empresa' => $empresa , 'status' => $status ]);

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
