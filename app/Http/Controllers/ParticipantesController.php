<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Direccion;
use App\Models\Facturacion;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Participante;    
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ParticipantesController extends Controller
{
    protected $pathInvIndex = 'Participante/Index';

    public function index()
    {
        return Inertia('Participante/Index');
    }

    public function get()
    {
        return Participante::with('tipoParticipante', 'tipo_doc')
            ->where('id_empresa', auth()->user()->id_empresa)
            ->where('isactive', true)
            ->get();
    }

    public function store(Request $request)
    {

        // Validar los datos de entrada
        $request->validate([
            'tipo_doc' => 'required|integer',
            'documento' => 'required|string',
            'nombres' => 'required|string',
            'apellido_pat' => 'required|string',
            'apellido_mat' => 'nullable|string',
        ]);

        // Crear o actualizar Persona
        $persona = Persona::firstOrNew(
            ['id_tipo_documento' => $request->tipo_doc, 'documento' => $request->documento]
        );

        $persona->nombres = strtoupper($request->nombres);
        $persona->apellido_paterno = strtoupper($request->apellido_pat);
        $persona->apellido_materno = strtoupper($request->apellido_mat);
        $persona->save();

        // Crear Participante
        $participante = Participante::create([
            'id_tipo_participante' => $request->id_tipo_participante, 
            'id_persona' => $persona->id,
            'id_empresa' => auth()->user()->id_empresa,
            'isactive' => true,
        ]);

        // Manejar la facturación si es necesario
        /*
        if ($request->filled('facturador')) {
            // Código de manejo de facturación aquí
        }

        // Actualizar la inscripción
        $inscripcion = Inscripcion::find($request->inscripcion['id']);
        if ($inscripcion) {
            // Código para actualizar inscripción aquí
        }
        */

        // Redirigir con éxito
        return redirect()->route('participantes.index', ['type' => $type]);
    }

    
    /*
    private function saveCuota($subTotal)
    {
        // Implementa el método saveCuota
        $cuota = new Cuota;
        $cuota->monto = $subTotal;
        $cuota->save();
        return $cuota;
    }
    */
}