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
            } else {
                $categoria->grupo = 'participante';
            }
        }

        $title = "Registration WMC 2026";


        return Inertia::render('Inscripcion/Index', compact('categorias', 'title'));
    }


    public function autor(Request $request)
    {
        return $this->renderInscripcion($request, '%AUTHOR%', "Author with special rate");
    }

    public function participante(Request $request)
    {
        return $this->renderInscripcion($request, '%GENERAL ATTENDEE%', "General Attendee");
    }

    private function renderInscripcion(Request $request, string $filtro, string $defaultTitle)
    {
        $section = $request->query('section', 'inscripciones');

        // Función anónima para reutilizar la lógica de precios vigentes
        $filtroPrecios = function ($query) {
            $query->where('fecha_inicio', '<=', $this->now)
                ->where('fecha_fin', '>=', $this->now);
        };

        // 1. Cargamos los Perfiles Principales (Inscripción al Congreso)
        $categorias = CategoriaInscripcion::with(['precio' => $filtroPrecios])
            ->where('nombre_en', 'LIKE', $filtro)
            ->where('isactive', true)
            ->orderBy('orden_es', 'ASC')
            ->get()
            ->map(function ($cat) {
                $cat->precio_disponible = $cat->precio->first();
                return $cat;
            });

        // 2. Cargamos los Adicionales (Tours y Cursos) - SIEMPRE se envían
        $adicionales = CategoriaInscripcion::with(['precio' => $filtroPrecios])
            ->where(function ($query) {
                $query->where('nombre_en', 'LIKE', '%TOUR%')
                    ->orWhere('nombre_en', 'LIKE', '%COURSE%');
            })
            ->where('isactive', true)
            ->get()
            ->map(function ($item) {
                $item->precio_disponible = $item->precio->first();
                return $item;
            });

        $title = ($section === 'viajes') ? "Tours & Courses" : $defaultTitle;

        return Inertia::render('Inscripcion/Inicio', compact('categorias', 'adicionales', 'title', 'section'));
    }


    public function getForm(Request $request)
    {
        // 1. Seguridad básica
        if (!str_contains($request->headers->get('referer'), 'registro') || (csrf_token() === null)) {
            abort(403, 'Unauthorized POST request.');
        }

        // 2. Captura de datos iniciales
        $id_tipo_documento = $request->input('id_tipo_documento') ?? $request->input('tipo_doc');
        $documento = trim($request->input('documento') ?? '');

        if (empty($id_tipo_documento) || empty($documento)) {
            return response()->json(['status' => false, 'message' => 'Document info missing'], 400);
        }

        // 3. Procesar Persona
        $persona = Persona::where('id_tipo_documento', $id_tipo_documento)
            ->where('documento', $documento)
            ->firstOrNew();

        // 4. Procesar Ocupación y Categoría
        $cargo = $request->input('cargo', '');
        $ocupacion_obj = Ocupacion::whereRaw("name like '%" . $cargo . "%'")
            ->where('isactive', true)
            ->first();

        $id_ocupacion = $ocupacion_obj ? $ocupacion_obj->id : 2795;

        $categoria = CategoriaInscripcion::findOrFail($request->input('selected_categoria'));
        // $precio_disponible = $categoria->precio
        //     ->where('fecha_inicio', '<=', $this->now)
        //     ->where('fecha_fin', '>=', $this->now)
        //     ->first();


        // dd($precio_disponible);
        $precio_disponible = $categoria->precio->filter(function ($p) {
            $hoy = Carbon::now();
            return $hoy->between(
                Carbon::parse($p->fecha_inicio)->startOfDay(),
                Carbon::parse($p->fecha_fin)->endOfDay()
            );
        })->first();

        // 2. Si por algún motivo el filtro de fechas falla, toma el precio por defecto de la categoría
        if (!$precio_disponible) {
            $precio_disponible = $categoria->precio->first();
        }

        // 3. Verificamos el valor
        $total = ($precio_disponible) ? $precio_disponible->valor : 0;

        // // Si después de todo sigue siendo 0, hay un problema grave de configuración
        // if ($total <= 0) {
        //     return response()->json(['status' => false, 'message' => 'Invalid amount (0)'], 400);
        // }

        $total = $precio_disponible->valor;

        $dias = '{"lun":1,"mar":1,"mie":1,"jue":1,"vie":1}';

        // 5. Lógica de One Day (CORREGIDA para evitar error de count())
        // if (str_contains(strtoupper($categoria->nombre_en), 'DAY') || str_contains(strtoupper($categoria->nombre_es), 'DIA')) {
        if (
            str_contains(strtoupper($categoria->nombre_en), ' DAY') ||
            (str_contains(strtoupper($categoria->nombre_es), ' DIA') && !str_contains(strtoupper($categoria->nombre_es), 'ESTUDIANTE'))
        ) {
            $selectedDays = $request->input('selectedDays', []);

            // Convertir string "mar,mie" a array si es necesario (FormData lo envía así aveces)
            if (is_string($selectedDays)) {
                $selectedDays = explode(',', $selectedDays);
            }

            $total = count($selectedDays) * $precio_disponible->valor;
            $dias_array = ["lun" => 0, "mar" => 0, "mie" => 0, "jue" => 0, "vie" => 0];

            foreach ($selectedDays as $sd) {
                $dia_key = strtolower(trim($sd));
                if (array_key_exists($dia_key, $dias_array)) {
                    $dias_array[$dia_key] = 1;
                }
            }
            $dias = json_encode($dias_array);
        }

        // 6. Guardar Dirección
        $direccion = ($persona->id_direccion > 0) ? Direccion::find($persona->id_direccion) : new Direccion;
        $direccion->id_pais = $request->input('pais');
        $direccion->id_departamento = $request->input('departamento', 0);
        $direccion->id_provincia = $request->input('provincia', 0);
        $direccion->id_distrito = $request->input('distrito', 0);
        $direccion->direccion = trim($request->input('direccionPersona', ''));
        $direccion->save();

        // 7. Guardar Persona
        $persona->id_direccion = $direccion->id;
        $persona->nombres = trim($request->input('nombres'));
        $persona->apellido_paterno = trim($request->input('apellido_paterno'));
        $persona->apellido_materno = $request->input('apellido_materno', '');
        $persona->correo = trim($request->input('correo'));
        $persona->celular = trim($request->input('celular'));
        $persona->sexo = $request->input('sexo');
        $persona->id_ocupacion = $id_ocupacion;
        $persona->id_nacionalidad = $request->input('nacionalidad', $request->input('pais')); // Fallback al país si no hay nacionalidad

        if ($request->filled('fecha_nacimiento')) {
            try {
                $persona->fecha_nacimiento = Carbon::parse($request->input('fecha_nacimiento'))->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error("Error parseando fecha: " . $e->getMessage());
            }
        }

        if (!$persona->exists) {
            $persona->id_tipo_documento = $id_tipo_documento;
            $persona->documento = $documento;
        }
        $persona->save();

        // 8. Crear Facturación
        $IGV = round(($total * 0.18), 2);

        $facturacion = new Facturacion;
        $facturacion->id_tipo_servicio = 4;
        $facturacion->id_moneda = $precio_disponible->moneda->id;
        $facturacion->id_tipo_pago = $request->input('selectTipoPago');
        $facturacion->tipo_doc_pago = $request->input('selectTipoDocPago');
        $facturacion->id_tipo_doc_facturador = $request->input('tipoDocumentoEmpresa');
        $facturacion->numero_doc_facturador = trim($request->input('documentoEmpresa'));
        $facturacion->nombre_facturador = trim($request->input('razonSocial'));
        $facturacion->direccion_facturador = trim($request->input('direccionEmpresa'));
        $facturacion->responsable_facturador = trim($request->input('responsable'));
        $facturacion->correo_facturador = trim($request->input('correo_facturador'));
        $facturacion->id_comprador = $persona->id;
        $facturacion->tipo_comprador = 'persona';
        $facturacion->IGV = $IGV;
        $facturacion->sub_total = floatval($total) - $IGV;
        $facturacion->detraccion = 0;
        $facturacion->total = $total;
        $facturacion->observacion = trim($request->input('empresa', ''));
        $facturacion->save();

        // 9. Crear Cuota (Agregado isactive para evitar error SQL)
        $cuota = new Cuota;
        $cuota->id_facturacion = $facturacion->id;
        $cuota->estado_pago = 'PENDIENTE';
        $cuota->isactive = true;
        $cuota->informacion = json_encode([
            "cuota" => "1",
            "valor" => (string)$total,
            "porcentaje" => "100",
            "estado_pago" => false
        ]);

        // dd($cuota);
        $cuota->save();

        // 10. Crear Inscripción
        $inscripcion = new Inscripcion;
        $inscripcion->id_persona = $persona->id;
        $inscripcion->id_categoria_inscripcion = $categoria->id;
        $inscripcion->id_facturacion = $facturacion->id;
        $inscripcion->usuario_creacion = $persona->id;
        $inscripcion->origen = 'web';
        $inscripcion->texto_cargo = $cargo;
        $inscripcion->dias = $dias;
        $inscripcion->autorizacion_datos = $request->input('auth', false);

        if ($request->hasFile('uploadDocument')) {
            $file = $request->file('uploadDocument');
            $name = 'insc_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(storage_path('app/public/documents'), $name);
            $inscripcion->document_path = asset('storage/documents/' . $name);
        }
        $inscripcion->save();

        // 11. Generar formulario de Niubiz
        try {
            $formNiubiz = app(\App\Http\Controllers\NiubizController::class)->getForm(
                $persona,
                $inscripcion,
                $facturacion,
                url()->previous(),
                url()->current()
            );

            $cuota->respuesta_api = $formNiubiz->k;
            $cuota->update();

            return response()->json([
                'status' => true,
                'formulario' => json_decode(base64_decode($formNiubiz->frm))
            ]);
        } catch (\Exception $e) {
            Log::error("Error en Niubiz: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error al contactar pasarela'], 500);
        }
    }


    public function niubizPayment($id, $order)
    {
        $facturacion = Facturacion::findOrFail($id);
        $cuota = $facturacion->cuotas->first();

        $transactiontoken = $_POST['transactionToken'] ?? null;

        if (!$transactiontoken) {
            return redirect('/')->with('error', 'Token de transacción no encontrado.');
        }

        $respuesta = app(\App\Http\Controllers\NiubizController::class)->authorization($cuota->respuesta_api, $facturacion->total, $transactiontoken, $order);
        $filtered_response = app(\App\Http\Controllers\NiubizController::class)->filterResponse($respuesta);

        $pasarela = Pasarela::where('id_evento', config('app.id_evento'))->where('codigo_tipo_pago', 'niubiz_tarjeta')->first();
        $niubiz = new Niubiz;

        // dd($filtered_response);
        // --- BLOQUE DE ERROR O DENEGADO ---
        if (isset($filtered_response['errorcode']) || is_null($filtered_response['transactionId']) || $filtered_response['transactionId'] == "") {


            $niubiz->num_orden = $order;
            $niubiz->codigo_tipo_pago = 'niubiz_tarjeta';

            // Importante: Guardamos el ACTION_CODE para mostrarlo en la vista de error
            $niubiz->estado = isset($filtered_response['ACTION_CODE']) ? $filtered_response['ACTION_CODE'] : ($filtered_response['errorcode'] ?? '666');

            $niubiz->monto = $facturacion->total;
            $niubiz->id_evento = config('app.id_evento');
            $niubiz->id_pasarela = $pasarela->id;
            $niubiz->id_compra = $cuota->id;

            // Detalle del error literal de Niubiz
            $niubiz->detalle = $filtered_response['ACTION_DESCRIPTION'] ?? "Transaction declined";

            $niubiz->fecha = date('Y-m-d');
            $niubiz->hora = date('H:i:s');
            $niubiz->save();

            return redirect('/pago/error/' . $niubiz->id);
        } else {
            // --- BLOQUE DE ÉXITO ---
            $niubiz->num_orden = $order;
            $niubiz->card_num = $filtered_response['CARD'] ?? '****';
            $niubiz->idtransaccion = $filtered_response['transactionId'];
            $niubiz->id_compra = $cuota->id;
            $niubiz->fecha = $filtered_response['date'] ?? date('Y-m-d');
            $niubiz->hora = $filtered_response['time'] ?? date('H:i:s');
            $niubiz->monto = $facturacion->total;

            // Capturar AUTHORIZATION CODE de la respuesta original
            $res_original = json_decode($respuesta);
            // Lo guardamos en 'detalle' para que la pantalla de confirmación lo muestre
            $niubiz->detalle = $res_original->dataMap->AUTHORIZATION_CODE ?? '000000';

            $niubiz->id_evento = config('app.id_evento');
            $niubiz->id_pasarela = $pasarela->id;
            $niubiz->codigo_tipo_pago = 'niubiz_tarjeta';
            $niubiz->estado = 'pagado';
            $niubiz->save();

            // Actualización de Cuota
            $cuota->informacion = json_encode([
                "cuota" => "1",
                "valor" => (string)$facturacion->total,
                "porcentaje" => "100",
                "estado_pago" => true
            ]);
            $cuota->estado_pago = 'PAGADO';
            $cuota->update();

            // Actualización de Inscripción
            $inscripcion = Inscripcion::where('id_facturacion', $facturacion->id)->first();
            $inscripcion->observacion = "Pagada Niubiz ID: " . $niubiz->id;
            $inscripcion->update();

            $persona = Persona::find($inscripcion->id_persona);

            $service_wmc = app(\App\Http\Controllers\WebServiceController::class)
                ->wsInscripcion_WMC_2026($facturacion, $persona, $inscripcion, $niubiz);

            // dd($service_wmc);
            try {
                Mail::to($persona->correo)->send(new \App\Mail\MailInscripcion($inscripcion, $niubiz));
            } catch (\Exception $e) {
                Log::error("Mail Error: " . $e->getMessage());
            }

            return redirect('/pago/confirmar/' . $inscripcion->id);
        }
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
}
