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

    protected function token()
    {
        return 'apis-token-13383.Aph50ddFaV03b9sZaRprJo5ZBpMz0yC4';
    }

    public function getData(Request $request)
    {
        $type = ($request->tipo_doc == 1) ? 'dni' : 'ruc';
        $data = $request->documento;

        if ($type == "dni") {
            $tipo_respuesta = 'persona';
            $urlApi = $this->urlApi . '/reniec/' . $type . '?numero=' . $data;
        }

        if ($type == "ruc") {
            $tipo_respuesta = 'empresa';
            $urlApi = $this->urlApi . '/sunat/ruc/full?numero=' . $data;
        }


        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token(),
            'Accept' => 'application/json'
        ])->get($urlApi);

        if ($request->ok()) {
            $request =   json_decode($request);
            if (isset($request->numeroDocumento)) {
                $response = [$tipo_respuesta => $request, 'status' => true];
            } else {
                $response = ['status' => false];
            }
        } else {
            $response = ['status' => false];
        }
        return $response;
    }

    public function getPersonData(Request $request)
    {

        if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
            abort(403, 'Unauthorized POST request.');
            exit;
        }

        /***  1. Validamos que se encuentre registrado ***/
        $tipo_documento = TipoDocumento::find($request->id_tipo_documento);
        $persona = Persona::where('id_tipo_documento', $request->id_tipo_documento)
            ->where('documento', $request->numero_documento)
            ->first();
        $status = true;

        // dd($persona);

        if ($persona) {
            $persona->pais = $persona->direccion->id_pais;
            $persona->departamento  = $persona->direccion->id_departamento;
            $persona->provincia  = $persona->direccion->id_provincia;
            $persona->distrito  = $persona->direccion->id_distrito;
            $persona->nacionalidad  = $persona->id_nacionalidad;
            $persona->direccionPersona  = $persona->direccion->direccion;
            $persona->cargo = $persona->ocupacion->name;
            $persona->ocupacion = $persona->ocupacion->name;
        } else {
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

        if ($request->id_tipo_documento == 1) {

            // $api_persona = $this->getData('dni', $request->numero_documento);
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 1, 'documento' => $request->numero_documento]);
            $api_persona = $this->getData($fakeRequest);

            if ($api_persona['status']) {
                $persona->nombres = $api_persona['persona']->nombres;
                $persona->apellido_paterno = $api_persona['persona']->apellidoPaterno;
                $persona->apellido_materno = $api_persona['persona']->apellidoMaterno;
            } else {
                $status = false;
            }
        }


        $persona->es_socio = app(\App\Http\Controllers\WebServiceController::class)
            ->validatePersonMember($request->id_tipo_documento, $request->numero_documento);

        // $persona->es_socio =true;

        return json_encode(['persona' => $persona, 'status' => $status]);
    }

    // public function getPersonData(Request $request)
    // {
    //     // 1. Validación de Seguridad
    //     if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
    //         abort(403, 'Unauthorized POST request.');
    //         exit;
    //     }

    //     // ---------------------------------------------------------
    //     // CORRECCIÓN: BÚSQUEDA POR HASH (BLIND INDEX)
    //     // ---------------------------------------------------------

    //     // A. Generamos el Hash del documento que ingresó el usuario
    //     // Usamos la misma 'app.key' que usamos para guardar en la BD.
    //     $docHash = hash_hmac('sha256', $request->numero_documento, config('app.key'));

    //     // B. Buscamos usando la columna HASH
    //     $persona = Persona::where('id_tipo_documento', $request->id_tipo_documento)
    //         ->where('documento_hash', $docHash) // <--- AQUÍ ESTÁ EL CAMBIO CLAVE
    //         ->first();

    //     $status = true;

    //     if ($persona) {
    //         // SI ENCONTRAMOS (BÚSQUEDA LOCAL):
    //         // Llenamos los datos desde nuestra BD.
    //         // Nota: Al acceder a $persona->direccion, Laravel desencripta solo si es necesario.
    //         $persona->pais = $persona->direccion->id_pais ?? 0;
    //         $persona->departamento  = $persona->direccion->id_departamento ?? 0;
    //         $persona->provincia  = $persona->direccion->id_provincia ?? 0;
    //         $persona->distrito  = $persona->direccion->id_distrito ?? 0;
    //         $persona->nacionalidad  = $persona->id_nacionalidad ?? 0;
    //         $persona->direccionPersona  = $persona->direccion->direccion ?? '';
    //         $persona->cargo = $persona->ocupacion->name ?? '';
    //         $persona->ocupacion = $persona->ocupacion->name ?? '';
    //     } else {
    //         // SI NO ENCONTRAMOS (NUEVO REGISTRO):
    //         // Creamos el objeto vacío para llenarlo
    //         $persona = new \stdClass();
    //         $persona->id_tipo_documento = $request->id_tipo_documento;
    //         $persona->documento = $request->numero_documento; // Pasamos el dato real para el form
    //         $persona->pais = 0;
    //         $persona->departamento  = 0;
    //         $persona->provincia  = 0;
    //         $persona->distrito  = 0;
    //         $persona->nacionalidad  = 0;
    //         $persona->direccionPersona  = "";
    //         $persona->cargo = "";
    //         $persona->ocupacion = "";
    //         $persona->celular = "";
    //         $persona->correo = "";
    //         $persona->sexo = 0;
    //         $persona->nombres = "";
    //         $persona->apellido_paterno = "";
    //         $persona->apellido_materno = "";
    //         $persona->fecha_nacimiento = $this->now;
    //     }

    //     // ---------------------------------------------------------
    //     // CONSULTA A RENIEC / API EXTERNA (SI ES DNI)
    //     // ---------------------------------------------------------
    //     if ($request->id_tipo_documento == 1) {
    //         try {
    //             $fakeRequest = new \Illuminate\Http\Request();
    //             $fakeRequest->merge(['tipo_doc' => 1, 'documento' => $request->numero_documento]);

    //             // Usamos un Try-Catch interno en getData si es posible, o aquí mismo
    //             $api_persona = $this->getData($fakeRequest);

    //             if (isset($api_persona['status']) && $api_persona['status']) {
    //                 // Si la API externa responde, llenamos los datos
    //                 // Usamos null coalescing (??) para evitar errores si falta algún campo
    //                 $persona->nombres = $api_persona['persona']->nombres ?? $persona->nombres;
    //                 $persona->apellido_paterno = $api_persona['persona']->apellidoPaterno ?? $persona->apellido_paterno;
    //                 $persona->apellido_materno = $api_persona['persona']->apellidoMaterno ?? $persona->apellido_materno;
    //             } else {
    //                 // Si la API dice false, no marcamos error general, dejamos que el usuario llene manual
    //                 // $status = false; // COMENTADO: Mejor dejar status true para que el usuario pueda escribir
    //             }
    //         } catch (\Exception $e) {
    //             // Si la API falla (Timeout), ignoramos y dejamos que el usuario llene manual
    //             \Illuminate\Support\Facades\Log::error("Error API DNI: " . $e->getMessage());
    //         }
    //     }

    //     // ---------------------------------------------------------
    //     // VALIDACIÓN DE SOCIO (WEBSERVICE)
    //     // ---------------------------------------------------------
    //     try {
    //         // Envolvemos en Try-Catch para evitar el error "cURL error 28" (Timeout)
    //         $persona->es_socio = app(\App\Http\Controllers\WebServiceController::class)
    //             ->validatePersonMember($request->id_tipo_documento, $request->numero_documento);
    //     } catch (\Exception $e) {
    //         \Illuminate\Support\Facades\Log::error("Error Validando Socio: " . $e->getMessage());
    //         $persona->es_socio = false; // Asumimos false si falla la conexión
    //     }

    //     return json_encode(['persona' => $persona, 'status' => $status]);
    // }

    public function getEmpresaData(Request $request)
    {
        $direccion = "";
        $status = true;

        // 1. Intentar buscar primero en nuestra base de datos local
        if ($request->tipo_doc == 1) { // DNI
            $empresa = Persona::where('id_tipo_documento', $request->tipo_doc)
                ->where('documento', $request->documento)
                ->first();
        } else { // RUC o Otros
            $empresa = Empresa::where('id_tipo_documento', $request->tipo_doc)
                ->where('documento', $request->documento)
                ->first();
        }

        // 2. Si existe localmente, preparamos la dirección y datos básicos
        if ($empresa) {
            if (!is_null($empresa->direccion)) {
                $empresa->pais = $empresa->direccion->id_pais;
                $empresa->departamento = intval($empresa->direccion->id_departamento) > 0 ? $empresa->direccion->id_departamento : 0;
                $empresa->provincia = intval($empresa->direccion->id_provincia) > 0 ? $empresa->direccion->id_provincia : 0;
                $empresa->distrito = intval($empresa->direccion->id_distrito) > 0 ? $empresa->direccion->id_distrito : 0;

                if ($empresa instanceof Persona) {
                    $empresa->nombre = trim($empresa->nombres . " " . $empresa->apellido_paterno . " " . $empresa->apellido_materno);
                    $empresa->telefono = $empresa->celular;
                }

                // Obtener nombres de ubicación para la cadena de dirección completa
                $dep = Departamento::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->first();
                $prov = Provincia::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->where('id_provincia', intval($empresa->provincia))->first();
                $dist = Distrito::where('id_pais', intval($empresa->pais))->where('id_departamento', intval($empresa->departamento))->where('id_provincia', intval($empresa->provincia))->where('id_distrito', intval($empresa->distrito))->first();

                $direccion = trim($empresa->direccion->direccion . " " . ($dep ? $dep->name : '') . " - " . ($prov ? $prov->name : '') . " - " . ($dist ? $dist->name : ''));
            }
        } else {
            // Inicializamos objeto vacío si no hay registro local
            $empresa = new \stdClass();
            $empresa->id_tipo_documento = $request->tipo_doc;
            $empresa->documento = $request->documento;
            $empresa->nombre = "";
            $status = false; // Cambiará a true si la API externa lo encuentra
        }

        // 3. CONSULTA A API EXTERNA (Solo para DNI y RUC)
        // Esto corrige el error de "Argument #1 ($request) must be of type Request"
        if ($request->tipo_doc == 1) { // DNI
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 1, 'documento' => $request->documento]);

            $api_res = $this->getData($fakeRequest);

            if ($api_res['status']) {
                $p = $api_res['persona'];
                $empresa->nombre = trim($p->nombres . " " . $p->apellidoPaterno . " " . $p->apellidoMaterno);
                $status = true;
            }
        }

        if ($request->tipo_doc == 2) { // RUC
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['tipo_doc' => 2, 'documento' => $request->documento]);

            $api_res = $this->getData($fakeRequest);

            if ($api_res['status']) {
                $e = $api_res['empresa'];
                $empresa->nombre = $e->razonSocial;
                // Construir dirección desde la API
                $direccion = trim($e->direccion . " " . $e->departamento . " - " . $e->provincia . " - " . $e->distrito);
                $status = true;
            }
        }

        $empresa->direccionEmpresa = $direccion;

        return response()->json([
            'empresa' => $empresa,
            'status' => $status
        ]);
    }
    public function validatePersonSoc(Request $request)
    {

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
