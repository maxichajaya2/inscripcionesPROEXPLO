<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Ocupacion;
use App\Models\Direccion;
use App\Models\Cuota;
use App\Models\Facturacion;
use App\Models\Pasarela;
use App\Models\Niubiz;
use App\Models\CategoriaInscripcion;
use App\Mail\MailInscripcion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;



class InscripcionController extends Controller
{
    public function __construct()
    {
        $this->now = Carbon::now()->format('Y-m-d');
    }

    // public function index()
    // {
    //     return Inertia::render('Inscripcion/Index');
    // }

    public function index()
    {
        $categorias = CategoriaInscripcion::query()
            ->where('isactive', true)
            ->where(function ($query) {
                $query->where('nombre_en', 'LIKE', '%AUTHOR%')
                    ->orWhere('nombre_en', 'LIKE', '%GENERAL ATTENDEE%');
            })
            ->orderBy('orden_es', 'ASC')
            ->get();

        foreach ($categorias as $categoria) {

            $categoria->precio_disponible = $categoria->precio
                ->where('fecha_inicio', '<=', $this->now)
                ->where('fecha_fin', '>=', $this->now)
                ->first();

            if (str_contains(strtoupper($categoria->nombre_en), 'AUTHOR')) {
                $categoria->grupo = 'autor';
            }
            // Si no, asumimos que es del grupo 'participante' (GENERAL ATTENDEE)
            else {
                $categoria->grupo = 'participante';
            }
        }

        $title = "Registration WMC 2026";


        return Inertia::render('Inscripcion/Index', compact('categorias', 'title'));
    }

    public function autor()
    {
        // Nota: El LIKE sigue buscando en 'nombre_es' porque entiendo que los registros
        // en tu BD aún se identifican así, pero el array de textos ahora usa llaves en inglés.
        $categorias = CategoriaInscripcion::where('nombre_en', 'LIKE', '%AUTHOR%')
            // ->where('es_beneficio', false)
            ->where('isactive', true)
            ->orderBy('orden_es', 'ASC')
            ->get();

        foreach ($categorias as $categoria) {
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)
                ->where('fecha_fin', '>=', $this->now)
                ->first();
        }

        $title = "Author with special rate";

        $modal_texts = [
            'MEMBER AUTHOR' => 'Special rate exclusively for Active Members of the IIMP whose current-year fees are duly settled and whose work has been selected.',

            'NON-MEMBER AUTHOR' => 'Rate for selected authors who do not qualify as students, faculty members, or IIMP members.',

            'FACULTY MEMBER AUTHOR' => 'Special rate for undergraduate faculty members whose work has been selected, upon presentation of valid proof of their status.',

            'STUDENT AUTHOR' => 'Rate for undergraduate students with selected papers, upon presentation of an updated proof of enrollment.',
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias', 'title', 'modal_texts'));
    }

    public function participante()
    {
        $categorias = CategoriaInscripcion::where('nombre_en', 'LIKE', '%GENERAL ATTENDEE%')
            // ->where('es_beneficio', false)
            ->where('isactive', true)
            ->orderBy('orden_es', 'ASC')
            ->get();

        foreach ($categorias as $categoria) {
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)
                ->where('fecha_fin', '>=', $this->now)
                ->first();
        }

        $title = "General Attendee";

        $modal_texts = [
            'GENERAL ATTENDEE – MEMBER' => 'Special rate exclusively for Active Members of the IIMP whose current-year fees are duly settled.',

            'GENERAL ATTENDEE – NON-MEMBER' => 'Rate applicable to the general public.',

            'GENERAL ATTENDEE – FACULTY MEMBER' => 'Special rate for undergraduate faculty members, who must present valid proof of their status.',

            'GENERAL ATTENDEE – STUDENT' => 'Rate applicable to undergraduate students in their 9th or 10th semester, upon presentation of an updated proof of enrollment.',

            'GENERAL ATTENDEE – ONE DAY' => 'This option gives participants access to all conferences scheduled for the selected day, as well as the exhibition area.',
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias', 'title', 'modal_texts'));
    }

    /***************  PRODUCCION **************** */
    /** ==================================== */

    public function getForm(Request $request)
    {

        if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
            abort(403, 'Unauthorized POST request.');
            exit;
        }

        $form_data = (object)$request->form;

        $persona = Persona::where('id_tipo_documento', $form_data->id_tipo_documento)->where('documento', $form_data->documento)->firstorNew();
        $ocupacion = Ocupacion::whereRaw("name like '%" . $form_data->cargo . "%'")->where('isactive', true)->first();
        $categoria = CategoriaInscripcion::find($form_data->selected_categoria);
        $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();

        $total = $categoria->precio_disponible->valor;
        $dias = '{"lun":1,"mar":1,"mie":1,"jue":1,"vie":1}';

        if (str_contains($categoria->nombre_es, 'DIA')) {

            $total = sizeof($form_data->selectedDays) * $categoria->precio_disponible->valor;
            $dias = json_decode($dias, true);

            foreach ($dias as $key => $dia) {
                $dias[$key] = 0;
            }

            foreach ($form_data->selectedDays as $selected_day) {
                $selected_day = strtolower($selected_day);
                $dias[$selected_day] = 1;
            }

            $dias = json_encode($dias);
        }

        if (!$ocupacion) {
            $ocupacion = 2795; //indice ocupacion no especificada o no se encuentra en el listado
        } else {
            $ocupacion = $ocupacion->id;
        }

        if (isset($persona->id_direccion)) {

            $persona->direccion->id_pais = $form_data->pais;
            $persona->direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0;
            $persona->direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0;
            $persona->direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0;
            $persona->direccion->direccion = $form_data->direccionPersona;
            $persona->direccion->update();
        } else {
            $direccion = new Direccion;
            $direccion->id_pais = $form_data->pais;
            $direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0;
            $direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0;
            $direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0;
            $direccion->direccion = trim($form_data->direccionPersona);
            $direccion->save();

            $persona->id_direccion = $direccion->id;
        }

        $persona->nombres = trim($form_data->nombres);
        $persona->apellido_paterno = trim($form_data->apellido_paterno);
        $persona->apellido_materno = isset($form_data->apellido_materno) ? trim($form_data->apellido_materno) : "";
        $persona->correo = trim($form_data->correo);
        $persona->celular = trim($form_data->celular);
        $persona->sexo = $form_data->sexo;
        $persona->id_ocupacion = $ocupacion;
        $persona->id_nacionalidad = $form_data->nacionalidad;
        $persona->fecha_nacimiento = Carbon::parse($form_data->fecha_nacimiento)->subDay()->format('Y-m-d');

        if (isset($persona->id)) {
            /*$fecha_nacimiento_actual = Carbon::parse($persona->fecha_nacimiento)->format('Y-m-d');

            if($fecha_nacimiento_actual != $fecha_nacimiento_nueva){
                $persona->fecha_nacimiento = $fecha_nacimiento_nueva;
            }*/

            $persona->update();
        } else {
            $persona->id_tipo_documento = $form_data->id_tipo_documento;
            $persona->documento = trim($form_data->documento);

            $persona->save();
        }

        $send = app(\App\Http\Controllers\WebServiceController::class)->wsPersona_create_update($persona);

        if (strlen($persona->sie_code) < 5) {
            $persona->sie_code = $send['sie_code'];
            $persona->update();
        }

        $IGV = round(($total * 0.18), 2);

        $facturacion = new Facturacion;
        $facturacion->id_tipo_servicio = 4; // servicio inscripciones perumin tabla tipo servicio
        $facturacion->id_moneda = $categoria->precio_disponible->moneda->id;
        $facturacion->id_tipo_pago = $form_data->selectTipoPago;
        $facturacion->tipo_doc_pago = $form_data->selectTipoDocPago;
        $facturacion->id_tipo_doc_facturador = $form_data->tipoDocumentoEmpresa;
        $facturacion->numero_doc_facturador = trim($form_data->documentoEmpresa);
        $facturacion->nombre_facturador = trim($form_data->razonSocial);
        $facturacion->direccion_facturador = trim($form_data->direccionEmpresa);
        $facturacion->responsable_facturador = trim($form_data->responsable);
        $facturacion->correo_facturador = trim($form_data->correo_facturador);
        $facturacion->id_comprador = $persona->id;
        $facturacion->tipo_comprador = 'persona';
        $facturacion->IGV = $IGV;
        $facturacion->sub_total = floatval($total) - $IGV;
        $facturacion->detraccion = 0;
        $facturacion->total = $total;
        $facturacion->observacion = trim($form_data->empresa);
        $facturacion->save();

        $informacion = json_decode('{
                    "cuota": "1",
                    "valor" : "' . $facturacion->total . '",
                    "porcentaje" : "100",
                    "estado_pago" : false
                }');

        $cuota = new Cuota;
        $cuota->informacion = $informacion;
        $cuota->isactive = true;
        $cuota->created_at = Carbon::now();
        $cuota->updated_at = Carbon::now();
        $cuota->id_facturacion = $facturacion->id;
        $cuota->estado_pago = 'PENDIENTE';
        $cuota->save();

        $inscripcion = new Inscripcion;
        $inscripcion->id_persona = $persona->id;
        $inscripcion->id_categoria_inscripcion = $form_data->selected_categoria;
        $inscripcion->id_facturacion = $facturacion->id;
        $inscripcion->usuario_creacion = $persona->id;
        $inscripcion->origen = 'web';
        $inscripcion->observacion = 'registro individual de persona, pendiente de pago';
        $inscripcion->credencial = trim($form_data->credencial);
        $inscripcion->autorizacion_datos = isset($form_data->auth) ? $form_data->auth : false;
        $inscripcion->texto_cargo = $form_data->cargo;
        $inscripcion->dias = $dias;

        if ($categoria->requiere_documento) {

            $document = $form_data->uploadDocument;

            if (!is_null($document)) {

                $documentName = 'inscripcion_' . time() . '.' . $document->getClientOriginalExtension();
                $inscripcion->document_type = $document->getClientMimeType();

                if (\App::environment('production')) {
                    $inscripcion->document_path = "https://inscripciones.wmc2026.org/storage/documents/" . $documentName;
                } else {
                    $inscripcion->document_path = "http://127.0.0.1:8000/storage/documents/" . $documentName;
                }

                $document->move(storage_path('app/public/documents'), $documentName);
            }
        }

        $inscripcion->save();

        $form = app(\App\Http\Controllers\NiubizController::class)->getForm($persona, $inscripcion, $facturacion, url()->previous(), url()->current());

        $cuota->respuesta_api = $form->k;
        $cuota->update();

        $formulario = json_decode(base64_decode($form->frm));

        return json_encode(['status' => true, 'formulario' => $formulario]);
    }


    public function niubizPayment($id, $order)
    {
        $facturacion = Facturacion::findOrFail($id);
        $cuota = $facturacion->cuotas->first();

        $transactiontoken = $_POST['transactionToken'];

        $respuesta = app(\App\Http\Controllers\NiubizController::class)->authorization($cuota->respuesta_api, $facturacion->total, $transactiontoken, $order);

        $filtered_response = app(\App\Http\Controllers\NiubizController::class)->filterResponse($respuesta);

        $pasarela = Pasarela::where('id_evento', config('app.id_evento'))->where('codigo_tipo_pago', 'niubiz_tarjeta')->first();
        $niubiz = new Niubiz;

        if (isset($filtered_response['errorcode']) || is_null($filtered_response['transactionId']) || $filtered_response['transactionId'] == "") {


            $niubiz->num_orden = $order;
            $niubiz->codigo_tipo_pago = 'niubiz_tarjeta';
            $niubiz->estado = $filtered_response['errorcode'];
            $niubiz->monto = $facturacion->total;
            $niubiz->id_evento = config('app.id_evento');
            $niubiz->id_pasarela = $pasarela->id;
            $niubiz->id_compra = $cuota->id;
            $niubiz->detalle = $filtered_response['ACTION_DESCRIPTION'];
            $niubiz->fecha = "-";
            $niubiz->hora = "-";
            $niubiz->save();

            return redirect('/pago/error/' . $niubiz->id);
        } else {

            $niubiz->num_orden = $order;
            $niubiz->card_num = $filtered_response['CARD'];
            $niubiz->idtransaccion = $filtered_response['transactionId'];
            $niubiz->id_compra = $cuota->id;
            $niubiz->fecha = $filtered_response['date'];
            $niubiz->hora = $filtered_response['time'];
            $niubiz->monto = $facturacion->total;
            $niubiz->detalle = $filtered_response['BRAND'];
            $niubiz->id_evento = config('app.id_evento');
            $niubiz->id_pasarela = $pasarela->id;
            $niubiz->codigo_tipo_pago = 'niubiz_tarjeta';
            $niubiz->estado = 'pagado';
            $niubiz->save();

            $informacion = json_decode('{
                    "cuota": "1",
                    "valor" : "' . $facturacion->total . '",
                    "porcentaje" : "100",
                    "estado_pago" : true
                }');

            $cuota->informacion = $informacion;
            $cuota->estado_pago = 'PAGADO';
            $cuota->update();

            $inscripcion = Inscripcion::where('id_facturacion', $facturacion->id)->first();
            $inscripcion->observacion = "registro facturacion persona, pagada niubiz id " . $niubiz->id;
            $inscripcion->update();

            $persona = Persona::find($inscripcion->id_persona);

            // $response= app(\App\Http\Controllers\WebServiceController::class)->wsInscripcion_create_update($facturacion, $persona, $inscripcion , $niubiz );
            //$response = ['status' => true];

            // if($response['status']){

            Mail::to($persona->correo)->send(new \App\Mail\MailInscripcion($inscripcion, $niubiz));

            return redirect('/pago/confirmar/' . $inscripcion->id);
            // }
        }

        return redirect('/');
    }


    public function confirmPayment($id)
    {

        $inscripcion = Inscripcion::find($id);
        $categoria = $inscripcion->categoria_inscripcion;
        $facturacion = $inscripcion->facturacion;
        $persona = $inscripcion->persona;
        $persona->nombre_completo = trim($persona->nombres . " " . $persona->apellido_paterno . " " . $persona->apellido_materno);
        $documento_persona = $persona->tipoDocumento;
        $documento_empresa = $facturacion->tipoDocumentoFacturador;
        $tipo_doc_pago = $facturacion->tipoDocumentoPago;
        $tipo_pago = $facturacion->tipoPago;
        $cuota = $facturacion->cuotas->first();

        if ($facturacion->id_tipo_pago == 3) { //tarjeta
            $pago = Niubiz::where('id_compra', $cuota->id)->first();
            $pago->digitos = substr($pago->card_num, -8);
        }

        return Inertia::render('Inscripcion/Confirmacion', compact('facturacion', 'pago', 'persona', 'categoria', 'documento_persona', 'documento_empresa', 'tipo_doc_pago', 'tipo_pago'));
    }

    /***************  LOCAL **************** */
    /** ==================================== */

    // public function getForm(Request $request)
    // {

    //     if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
    //         abort(403, 'Unauthorized POST request.');
    //         exit;
    //     }

    //     $form_data = (object)$request->form;

    //     $persona = Persona::where('id_tipo_documento', $form_data->id_tipo_documento)
    //         ->where('documento', $form_data->documento)
    //         ->firstorNew();
    //     $ocupacion = Ocupacion::whereRaw("name like '%" . $form_data->cargo . "%'")
    //         ->where('isactive', true)
    //         ->first();
    //     $categoria = CategoriaInscripcion::find($form_data->selected_categoria);
    //     $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)
    //         ->where('fecha_fin', '>=', $this->now)
    //         ->first();

    //     $total = $categoria->precio_disponible->valor;
    //     $dias = '{"lun":1,"mar":1,"mie":1,"jue":1,"vie":1}';

    //     if (!$ocupacion) {
    //         $ocupacion = 2795; //indice ocupacion no especificada o no se encuentra en el listado
    //     } else {
    //         $ocupacion = $ocupacion->id;
    //     }

    //     //    PERSONA
    //     //  ========================================

    //     if (isset($persona->id_direccion)) {

    //         $persona->direccion->id_pais = $form_data->pais;
    //         $persona->direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0;
    //         $persona->direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0;
    //         $persona->direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0;
    //         $persona->direccion->direccion = $form_data->direccionPersona;
    //         $persona->direccion->update();
    //     } else {
    //         $direccion = new Direccion;
    //         $direccion->id_pais = $form_data->pais;
    //         $direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0;
    //         $direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0;
    //         $direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0;
    //         $direccion->direccion = trim($form_data->direccionPersona);
    //         $direccion->save();

    //         $persona->id_direccion = $direccion->id;
    //     }
    //     $persona->nombres = trim($form_data->nombres);
    //     $persona->apellido_paterno = trim($form_data->apellido_paterno);
    //     $persona->apellido_materno = isset($form_data->apellido_materno) ? trim($form_data->apellido_materno) : "";
    //     $persona->correo = trim($form_data->correo);
    //     $persona->celular = trim($form_data->celular);
    //     $persona->sexo = $form_data->sexo;
    //     $persona->id_ocupacion = $ocupacion;
    //     $persona->id_nacionalidad = $form_data->nacionalidad;
    //     $persona->fecha_nacimiento = Carbon::parse($form_data->fecha_nacimiento)->subDay()->format('Y-m-d');

    //     if (isset($persona->id)) {
    //         /*$fecha_nacimiento_actual = Carbon::parse($persona->fecha_nacimiento)->format('Y-m-d');

    //         if($fecha_nacimiento_actual != $fecha_nacimiento_nueva){
    //             $persona->fecha_nacimiento = $fecha_nacimiento_nueva;
    //         }*/

    //         $persona->update();
    //     } else {
    //         $persona->id_tipo_documento = $form_data->id_tipo_documento;
    //         $persona->documento = trim($form_data->documento);

    //         $persona->save();
    //     }
    //     //    SERVICIO JOHN MORON
    //     //  ========================================
    //     $send = app(\App\Http\Controllers\WebServiceController::class)
    //         ->wsPersona_create_update($persona);

    //     if (strlen($persona->sie_code) < 5) {
    //         $persona->sie_code = $send['sie_code'];
    //         $persona->update();
    //     }

    //     //    CATEGORIAS POR DIA
    //     //  ========================================
    //     if (str_contains($categoria->nombre_es, 'DIA')) {

    //         $total = sizeof($form_data->selectedDays) * $categoria->precio_disponible->valor;
    //         $dias = json_decode($dias, true);

    //         foreach ($dias as $key => $dia) {
    //             $dias[$key] = 0;
    //         }

    //         foreach ($form_data->selectedDays as $selected_day) {
    //             $selected_day = strtolower($selected_day);
    //             $dias[$selected_day] = 1;
    //         }

    //         $dias = json_encode($dias);
    //     }


    //     //    FACTURACION
    //     //  ========================================
    //     $IGV = round(($total * 0.18), 2);

    //     $facturacion = new Facturacion;
    //     $facturacion->id_tipo_servicio = 4; //  servicio inscripciones perumin tabla tipo servicio(cualquier tipo de inscripcions)
    //     $facturacion->id_moneda = $categoria->precio_disponible->moneda->id;
    //     $facturacion->id_tipo_pago = $form_data->selectTipoPago;
    //     $facturacion->tipo_doc_pago = $form_data->selectTipoDocPago;
    //     $facturacion->id_tipo_doc_facturador = $form_data->tipoDocumentoEmpresa;
    //     $facturacion->numero_doc_facturador = trim($form_data->documentoEmpresa);
    //     $facturacion->nombre_facturador = trim($form_data->razonSocial);
    //     $facturacion->direccion_facturador = trim($form_data->direccionEmpresa);
    //     $facturacion->responsable_facturador = trim($form_data->responsable);
    //     $facturacion->correo_facturador = trim($form_data->correo_facturador);
    //     $facturacion->id_comprador = $persona->id;
    //     $facturacion->tipo_comprador = 'persona';
    //     $facturacion->IGV = $IGV;
    //     $facturacion->sub_total = floatval($total) - $IGV;
    //     $facturacion->detraccion = 0;
    //     $facturacion->total = $total;
    //     $facturacion->observacion = trim($form_data->empresa);
    //     $facturacion->save();

    //     $informacion = json_decode('{
    //                 "cuota": "1",
    //                 "valor" : "' . $facturacion->total . '",
    //                 "porcentaje" : "100",
    //                 "estado_pago" : false
    //             }');

    //     //    CUOTA
    //     //  ========================================
    //     $cuota = new Cuota;
    //     $cuota->informacion = $informacion;
    //     $cuota->isactive = true;
    //     $cuota->created_at = Carbon::now();
    //     $cuota->updated_at = Carbon::now();
    //     $cuota->id_facturacion = $facturacion->id;
    //     $cuota->estado_pago = 'PENDIENTE';
    //     $cuota->save();

    //     //    INSCRIPCION
    //     //  ========================================
    //     $inscripcion = new Inscripcion;
    //     $inscripcion->id_persona = $persona->id;
    //     $inscripcion->id_categoria_inscripcion = $form_data->selected_categoria;
    //     $inscripcion->id_facturacion = $facturacion->id;
    //     $inscripcion->usuario_creacion = $persona->id;
    //     $inscripcion->isactive = true;
    //     $inscripcion->origen = 'web';
    //     $inscripcion->observacion = 'registro individual de persona, pendiente de pago';
    //     $inscripcion->credencial = trim($form_data->credencial);
    //     $inscripcion->autorizacion_datos = isset($form_data->auth) ? $form_data->auth : false;
    //     $inscripcion->texto_cargo = $form_data->cargo;
    //     $inscripcion->dias = $dias;

    //     if ($categoria->requiere_documento) {

    //         $document = $form_data->uploadDocument;

    //         if (!is_null($document)) {

    //             $documentName = 'inscripcion_' . time() . '.' . $document->getClientOriginalExtension();
    //             $inscripcion->document_type = $document->getClientMimeType();

    //             if (\App::environment('production')) {
    //                 $inscripcion->document_path = "https://inscripciones.perumin.com/storage/documents/" . $documentName;
    //             } else {
    //                 $inscripcion->document_path = "http://127.0.0.1:8000/storage/documents/" . $documentName;
    //             }

    //             $document->move(storage_path('app/public/documents'), $documentName);
    //         }
    //     }

    //     $inscripcion->save();

    //     //   NIUBIZ
    //     //  ========================================
    //     $form = app(\App\Http\Controllers\NiubizController::class)->getForm($persona, $inscripcion, $facturacion, url()->previous(), url()->current());


    //     $cuota->respuesta_api = $form->k;
    //     $cuota->update();

    //     $formulario = json_decode(base64_decode($form->frm));

    //     // dd( $formulario );

    //     return json_encode(['status' => true, 'formulario' => $formulario]);
    // }

    // public function niubizPayment(Request $request, $id, $order)
    // {
    //     // 1. Validar Token
    //     $transactiontoken = $request->input('transactionToken');

    //     if (!$transactiontoken) {
    //         return response()->json(['status' => 'error', 'message' => 'No se generó el token de pago.']);
    //     }

    //     // 2. Cargar datos
    //     $facturacion = \App\Models\Facturacion::findOrFail($id);
    //     $cuota = $facturacion->cuotas->first();

    //     // 3. LLAMAR A NIUBIZ
    //     $niubizCtrl = app(\App\Http\Controllers\NiubizController::class);
    //     $datos = $niubizCtrl->authorization($cuota->respuesta_api, $facturacion->total, $transactiontoken, $order);

    //     // ---------------------------------------------------------
    //     // 4. EXTRACCIÓN DE DATOS (Lógica unificada)
    //     // ---------------------------------------------------------

    //     // Si viene 'dataMap' (éxito) o 'data' (error), lo capturamos.
    //     $origen = $datos['dataMap'] ?? ($datos['data'] ?? []);

    //     // Extraemos CÓDIGO (Ej: 000, 116, 400)
    //     $actionCode = $origen['ACTION_CODE']
    //         ?? ($origen['actionCode']
    //             ?? ($datos['errorCode']
    //                 ?? 'default'));

    //     // Extraemos MENSAJE (Ej: "Fondos insuficientes")
    //     $message = $origen['ACTION_DESCRIPTION']
    //         ?? ($origen['actionDescription']
    //             ?? ($datos['errorMessage']
    //                 ?? 'Error desconocido'));

    //     // Extraemos INFO TARJETA
    //     $transactionId = $origen['TRANSACTION_ID'] ?? ($datos['order']['transactionId'] ?? '0');
    //     $cardNum       = $origen['CARD'] ?? '****';
    //     $brand         = $origen['BRAND'] ?? 'Unknown';

    //     // 5. PREPARAR MODELO (Sin guardar aún)
    //     $niubiz = new \App\Models\Niubiz;
    //     $niubiz->num_orden = $order;
    //     $niubiz->monto = $facturacion->total;
    //     $niubiz->id_compra = $cuota->id;
    //     $niubiz->id_evento = config('app.id_evento');
    //     $niubiz->id_pasarela = 5;
    //     $niubiz->codigo_tipo_pago = 'niubiz_tarjeta';
    //     $niubiz->fecha = date('d/m/Y');
    //     $niubiz->hora = date('H:i:s');
    //     $niubiz->card_num = $cardNum;
    //     $niubiz->idtransaccion = $transactionId;

    //     // ---------------------------------------------------------
    //     // 6. VALIDAR ÉXITO O ERROR
    //     // ---------------------------------------------------------

    //     if ($actionCode === '000') {
    //         // --- ✅ ÉXITO ---
    //         $niubiz->estado = 'pagado';
    //         $niubiz->detalle = 'Aprobado (' . $brand . ')';
    //         $niubiz->save();

    //         $cuota->estado_pago = 'PAGADO';
    //         $cuota->respuesta_api = json_encode($datos);
    //         $cuota->update();

    //         $inscripcion = \App\Models\Inscripcion::where('id_facturacion', $facturacion->id)->first();
    //         $persona = \App\Models\Persona::find($inscripcion->id_persona);

    //         // --- 🌐 WEB SERVICE (Agregado de nuevo por si lo necesitas) ---
    //         try {
    //             $wsCtrl = app(\App\Http\Controllers\WebServiceController::class);
    //             $wsCtrl->wsInscripcion_create_update($facturacion, $persona, $inscripcion, $niubiz);
    //         } catch (\Exception $e) {
    //             Log::error("Error WebService: " . $e->getMessage());
    //         }

    //         // --- 📧 EMAIL ---
    //         try {
    //             \Illuminate\Support\Facades\Mail::to($persona->correo)
    //                 ->send(new \App\Mail\MailInscripcion($inscripcion, $niubiz));
    //         } catch (\Exception $e) {
    //             Log::error("Error Email: " . $e->getMessage());
    //         }

    //         return redirect('/pago/confirmar/' . $inscripcion->id);
    //     } else {
    //         // --- ❌ ERROR (Aquí entra el código 116) ---
    //         $niubiz->estado = 'error';
    //         $niubiz->detalle = $message;
    //         $niubiz->save();

    //         // Envio LIMPIO a Vue
    //         return \Inertia\Inertia::render('Pago/Error', [
    //             'pago' => [
    //                 'action_code' => (string)$actionCode, // "116"
    //                 'detalle'     => $message,            // "Fondos insuficientes"
    //                 'monto'       => (string)$niubiz->monto
    //             ]
    //         ]);
    //     }
    // }

    // public function confirmPayment($id)
    // {

    //     $inscripcion = Inscripcion::find($id);

    //     $categoria = $inscripcion->categoria_inscripcion; //ok
    //     $facturacion = $inscripcion->facturacion; //
    //     $persona = $inscripcion->persona;
    //     $persona->nombre_completo = trim($persona->nombres . " " . $persona->apellido_paterno . " " . $persona->apellido_materno);
    //     $documento_persona = $persona->tipoDocumento;
    //     $documento_empresa = $facturacion->tipoDocumentoFacturador;
    //     $tipo_doc_pago = $facturacion->tipoDocumentoPago;
    //     $tipo_pago = $facturacion->tipoPago;
    //     $cuota = $facturacion->cuotas->first();

    //     // dd($cuota);

    //     // if ($facturacion->id_tipo_pago == 3) { //tarjeta
    //     //     $pago = Niubiz::where('id_compra', $cuota->id)->first();
    //     //     $pago->digitos = substr($pago->card_num, -8);
    //     // }

    //     if ($facturacion->id_tipo_pago == 3) { // Tarjeta
    //         $pago = Niubiz::where('id_compra', $cuota->id)->first();

    //         // Solo intentamos extraer los dígitos si el registro existe
    //         if ($pago) {
    //             $pago->digitos = substr($pago->card_num, -8);
    //         } else {
    //             // Creamos un objeto vacío o con valores por defecto para evitar errores en la vista
    //             $pago = (object)['digitos' => '****', 'detalle' => 'Tarjeta'];
    //         }
    //     }

    //     return Inertia::render('Inscripcion/Confirmacion', compact('facturacion', 'pago', 'persona', 'categoria', 'documento_persona', 'documento_empresa', 'tipo_doc_pago', 'tipo_pago'));
    // }
}
