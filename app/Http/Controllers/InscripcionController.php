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



class InscripcionController extends Controller
{
    public function __construct()
    {
        $this->now = Carbon::now()->format('Y-m-d');
    }

    public function index(){
        return Inertia::render('Inscripcion/Index');
    }

    public function convencionista(){
        $categorias = CategoriaInscripcion::whereRaw("nombre_es like '%CONVENCIONISTA%'")->where('es_beneficio',false)->where('isactive',true)->orderBy('orden_es','ASC')->get();

        foreach($categorias as $categoria){
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();
        }

        $title = "Convencionista";

        $modal_texts = [
            "Convencionista No Asociado" => "Tarifa dirigida a todo el público en general. El participante que se inscriba en esta categoría tendrá acceso a las actividades del programa durante la semana del evento y visita a la Exhibición Tecnológica Minera - EXTEMIN.",
            "Convencionista Asociado" => "Beneficio exclusivo para los socios activos del IIMP (al día en su cuota del año en curso). Permite el acceso a todas las actividades del programa durante la semana del evento y visita a la Exhibición Tecnológica Minera - EXTEMIN. Deberá contar con una membresía mínima de 3 meses.",
            "Convencionista por día" => "Tarifa que permite elegir el o los días en que asistirá a PERUMIN 37 Convención Minera. El participante que se inscriba en esta categoría tendrá acceso a las actividades del programa y visita a la Exhibición Tecnológica Minera - EXTEMIN durante el día elegido.",
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias','title','modal_texts'));
    }

    public function extemin(){
        $categorias = CategoriaInscripcion::whereRaw("nombre_es like '%EXTEMIN%'")->where('es_beneficio',false)->where('isactive',true)->orderBy('orden_es','ASC')->get();

        foreach($categorias as $categoria){
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();
        }

        $title = "Extemin";

        $modal_texts = [
            "EXTEMIN por día" => "Tarifa que permite el acceso solo a la Exhibición Tecnológica Minera (EXTEMIN), eligiendo el o los días en que asistirá a la Exhibición.",
            "EXTEMIN por semana" => "Tarifa que permite el acceso solo a la Exhibición Tecnológica Minera (EXTEMIN) durante la semana completa.",
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias','title','modal_texts'));
    }

    public function docente(){
        $categorias = CategoriaInscripcion::whereRaw("nombre_es like '%DOCENTE%'")->where('es_beneficio',false)->where('isactive',true)->orderBy('orden_es','ASC')->get();

        foreach($categorias as $categoria){
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();
        }

        $title = "Docente";

        $modal_texts = [
            "Docente Universitario" => "Tarifa preferencial para docentes universitarios de pregrado a tiempo completo. El participante que se inscriba en esta categoría tendrá acceso a las actividades del programa durante la semana del evento y visita a la Exhibición Tecnológica Minera - EXTEMIN. Para acceder a esta tarifa es obligatorio presentar una carta de la institución donde labora. (No aplica a docentes de post grado)."
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias','title','modal_texts'));
    }

    public function estudiante(){
        $categorias = CategoriaInscripcion::whereRaw("nombre_es like '%ESTUDIANTE%'")->where('es_beneficio',false)->where('isactive',true)->orderBy('orden_es','ASC')->get();

        foreach($categorias as $categoria){
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();
        }

        $title = "Estudiante";

        $modal_texts = [
            "Estudiante Universitario" => "Beneficio preferencial para los estudiantes universitarios que cursen el 10° ciclo. Para acceder a esta tarifa es obligatorio presentar una constancia de pertenencia al quinto superior. El participante que se inscriba en esta categoría tendrá acceso a las actividades del programa durante la semana del evento y visita a la Exhibición Tecnológica Minera - EXTEMIN. Capacidad limitada: 300."
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias','title','modal_texts'));
    }

    public function asociado_sme(){
        $categorias = CategoriaInscripcion::whereRaw("nombre_es like '%SME%'")->where('es_beneficio',false)->where('isactive',true)->orderBy('orden_es','ASC')->get();

        foreach($categorias as $categoria){
            $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();
        }

        $title = "Asociado SME";

        $modal_texts = [
            "Estudiante Universitario" => "Beneficio exclusivo para los Asociados del SME (al día en su cuota del año en curso). Permite el acceso a todas las actividades del programa durante la semana del evento y visita a la Exhibición Tecnológica Minera - EXTEMIN."
        ];

        return Inertia::render('Inscripcion/Inicio', compact('categorias','title','modal_texts'));
    }

    public function getForm(Request $request){

        if ( !str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null ) ){
            abort(403, 'Unauthorized POST request.');
            exit;
        }

        $form_data = (Object)$request->form;

        $persona = Persona::where('id_tipo_documento',$form_data->id_tipo_documento)->where('documento',$form_data->documento)->firstorNew();
        $ocupacion = Ocupacion::whereRaw("name like '%". $form_data->cargo."%'")->where('isactive',true)->first();
        $categoria = CategoriaInscripcion::find($form_data->selected_categoria);
        $categoria->precio_disponible = $categoria->precio->where('fecha_inicio', '<=', $this->now)->where('fecha_fin', '>=', $this->now)->first();

        $total = $categoria->precio_disponible->valor;
        $dias = '{"lun":1,"mar":1,"mie":1,"jue":1,"vie":1}';

        if(str_contains($categoria->nombre_es, 'DIA')){

            $total = sizeof($form_data->selectedDays) * $categoria->precio_disponible->valor;
            $dias = json_decode($dias, true);

            foreach($dias as $key => $dia){
                $dias[$key] = 0;
            }

            foreach($form_data->selectedDays as $selected_day){
                $selected_day = strtolower($selected_day);
                $dias[ $selected_day] = 1;
            }

            $dias = json_encode($dias);
        }

        if(!$ocupacion){
            $ocupacion = 2795; //indice ocupacion no especificada o no se encuentra en el listado
        }else{
            $ocupacion = $ocupacion->id;
        }

        if(isset($persona->id_direccion)){

            $persona->direccion->id_pais = $form_data->pais;
            $persona->direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0 ;
            $persona->direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0 ;
            $persona->direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0 ;
            $persona->direccion->direccion = $form_data->direccionPersona;
            $persona->direccion->update();

        }else{
            $direccion = new Direccion;
            $direccion->id_pais = $form_data->pais;
            $direccion->id_departamento = isset($form_data->departamento) ? $form_data->departamento : 0 ;
            $direccion->id_provincia = isset($form_data->provincia) ? $form_data->provincia : 0 ;
            $direccion->id_distrito = isset($form_data->distrito) ? $form_data->distrito : 0 ;
            $direccion->direccion = trim($form_data->direccionPersona);
            $direccion->save();

            $persona->id_direccion = $direccion->id;
        }

        $persona->nombres = trim($form_data->nombres);
        $persona->apellido_paterno = trim($form_data->apellido_paterno);
        $persona->apellido_materno = isset($form_data->apellido_materno) ? trim($form_data->apellido_materno): "";
        $persona->correo = trim($form_data->correo);
        $persona->celular = trim($form_data->celular);
        $persona->sexo = $form_data->sexo;
        $persona->id_ocupacion = $ocupacion;
        $persona->id_nacionalidad = $form_data->nacionalidad;
        $persona->fecha_nacimiento = Carbon::parse($form_data->fecha_nacimiento)->subDay()->format('Y-m-d');

        if(isset($persona->id)){
            /*$fecha_nacimiento_actual = Carbon::parse($persona->fecha_nacimiento)->format('Y-m-d');

            if($fecha_nacimiento_actual != $fecha_nacimiento_nueva){
                $persona->fecha_nacimiento = $fecha_nacimiento_nueva;
            }*/

            $persona->update();
        }else{
            $persona->id_tipo_documento = $form_data->id_tipo_documento;
            $persona->documento = trim( $form_data->documento);

            $persona->save();
        }

        $send = app(\App\Http\Controllers\WebServiceController::class)->wsPersona_create_update($persona);

        if(strlen($persona->sie_code) < 5){
            $persona->sie_code = $send['sie_code'];
            $persona->update();
        }

        $IGV = round( ($total * 0.18) , 2);

        $facturacion = new Facturacion;
        $facturacion->id_tipo_servicio = 4; // servicio inscripciones perumin tabla tipo servicio
        $facturacion->id_moneda = $categoria->precio_disponible->moneda->id;
        $facturacion->id_tipo_pago = $form_data->selectTipoPago;
        $facturacion->tipo_doc_pago = $form_data->selectTipoDocPago;
        $facturacion->id_tipo_doc_facturador = $form_data->tipoDocumentoEmpresa;
        $facturacion->numero_doc_facturador = trim($form_data->documentoEmpresa);
        $facturacion->nombre_facturador = trim($form_data->razonSocial);
        $facturacion->direccion_facturador = trim( $form_data->direccionEmpresa);
        $facturacion->responsable_facturador = trim( $form_data->responsable);
        $facturacion->correo_facturador = trim( $form_data->correo_facturador);
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

        if($categoria->requiere_documento){

            $document = $form_data->uploadDocument;

            if (!is_null($document)) {

                $documentName = 'inscripcion_'.time() . '.' . $document->getClientOriginalExtension();
                $inscripcion->document_type = $document->getClientMimeType();

                if(\App::environment('production')){
                    $inscripcion->document_path = "https://inscripciones.perumin.com/storage/documents/" . $documentName;
                }else{
                    $inscripcion->document_path = "http://127.0.0.1:8000/storage/documents/" . $documentName;
                }

                $document->move( storage_path('app/public/documents'), $documentName);
            }
        }

        $inscripcion->save();

        $form = app(\App\Http\Controllers\NiubizController::class)->getForm($persona, $inscripcion, $facturacion, url()->previous() , url()->current() );

        $cuota->respuesta_api = $form->k;
        $cuota->update();

        $formulario = json_decode( base64_decode($form->frm));

        return json_encode(['status' => true , 'formulario' => $formulario]);

    }

    public function niubizPayment($id, $order){
        $facturacion = Facturacion::findOrFail($id);
        $cuota = $facturacion->cuotas->first();

        $transactiontoken = $_POST['transactionToken'];

        $respuesta = app(\App\Http\Controllers\NiubizController::class)->authorization($cuota->respuesta_api, $facturacion->total, $transactiontoken, $order);

        /*$respuesta = '{
            "header": {
                "ecoreTransactionUUID": "3746e2a1-19bb-4251-b920-f7d2cc7c7c6e",
                "ecoreTransactionDate": 1749744006879,
                "millis": 958
            },
            "fulfillment": {
                "channel": "web",
                "merchantId": "456879853",
                "terminalId": "00000001",
                "captureType": "manual",
                "countable": true,
                "fastPayment": false,
                "signature": "3746e2a1-19bb-4251-b920-f7d2cc7c7c6e"
            },
            "order": {
                "tokenId": "3624210E49BA4F80A4210E49BA4F80E0",
                "purchaseNumber": "8291",
                "amount": 1900,
                "installment": 0,
                "currency": "USD",
                "authorizedAmount": 1900,
                "authorizationCode": "091800",
                "actionCode": "000",
                "traceNumber": "31645",
                "transactionDate": "250612110006",
                "transactionId": "993211570048581"
            },
            "dataMap": {
                "TERMINAL": "00000001",
                "BRAND_ACTION_CODE": "00",
                "BRAND_HOST_DATE_TIME": "201222141839",
                "TRACE_NUMBER": "31645",
                "CARD_TYPE": "D",
                "ECI_DESCRIPTION": "Transaccion no autenticada pero enviada en canal seguro",
                "SIGNATURE": "3746e2a1-19bb-4251-b920-f7d2cc7c7c6e",
                "CARD": "447411******2240",
                "MERCHANT": "109705108",
                "STATUS": "Authorized",
                "ACTION_DESCRIPTION": "Aprobado y completado con exito",
                "ID_UNICO": "993211570048581",
                "AMOUNT": "1900.0",
                "AUTHORIZATION_CODE": "091800",
                "YAPE_ID": "",
                "CURRENCY": "0604",
                "TRANSACTION_DATE": "250612110006",
                "ACTION_CODE": "000",
                "CVV2_VALIDATION_RESULT": "M",
                "ECI": "07",
                "ID_RESOLUTOR": "420201222142237",
                "BRAND": "visa",
                "ADQUIRENTE": "570002",
                "BRAND_NAME": "VI",
                "PROCESS_CODE": "000000",
                "TRANSACTION_ID": "993211570048581"
            }
        }';*/

        $filtered_response = app(\App\Http\Controllers\NiubizController::class)->filterResponse($respuesta);

        $pasarela = Pasarela::where('id_evento', config('app.id_evento'))->where('codigo_tipo_pago','niubiz_tarjeta')->first();
        $niubiz = new Niubiz;

        if (isset($filtered_response['errorcode'])||is_null($filtered_response['transactionId']) || $filtered_response['transactionId']=="") {

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

                return redirect('/pago/error/'. $niubiz->id);
		}else{

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
            $inscripcion->observacion = "registro facturacion persona, pagada niubiz id ".$niubiz->id;
            $inscripcion->update();

            $persona = Persona::find($inscripcion->id_persona);

            $response= app(\App\Http\Controllers\WebServiceController::class)->wsInscripcion_create_update($facturacion, $persona, $inscripcion , $niubiz );
            //$response = ['status' => true];

            if($response['status']){

                Mail::to($persona->correo)->send(new \App\Mail\MailInscripcion( $inscripcion, $niubiz));

                return redirect('/pago/confirmar/'.$inscripcion->id);
            }
        }

        return redirect('/');

    }

    public function confirmPayment($id){

        $inscripcion = Inscripcion::find($id);
        $categoria = $inscripcion->categoria_inscripcion;
        $facturacion = $inscripcion->facturacion;
        $persona = $inscripcion->persona;
        $persona->nombre_completo = trim($persona->nombres." ".$persona->apellido_paterno." ".$persona->apellido_materno);
        $documento_persona = $persona->tipoDocumento;
        $documento_empresa = $facturacion->tipoDocumentoFacturador;
        $tipo_doc_pago = $facturacion->tipoDocumentoPago;
        $tipo_pago = $facturacion->tipoPago;
        $cuota = $facturacion->cuotas->first();

        if($facturacion->id_tipo_pago == 3){ //tarjeta
            $pago = Niubiz::where('id_compra',$cuota->id)->first();
            $pago->digitos = substr( $pago->card_num ,-8);
        }

        return Inertia::render('Inscripcion/Confirmacion', compact('facturacion','pago','persona','categoria','documento_persona','documento_empresa','tipo_doc_pago','tipo_pago'));

    }




}
