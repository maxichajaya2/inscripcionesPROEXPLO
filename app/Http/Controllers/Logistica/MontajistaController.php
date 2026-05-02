<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class MontajistaController extends Controller
{
    protected $connection = 'pgsql_second';

    public function index()
    {
        $personal_montaje = DB::connection($this->connection)
            ->table('personal_montaje')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Logistica/Montajista/Index', [
            'personal_montaje' => $personal_montaje,
        ]);
    }

    public function escaner()
    {
        return Inertia::render('Logistica/Montajista/Escaner');
    }

    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:15',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'nullable|email|max:255',
            'cargo' => 'nullable|string|max:100',
            'ruc_empresa' => 'required|string|max:11',
            'nombre_empresa' => 'required|string|max:255',
            'codigo_qr' => ['required', 'string', 'max:100', Rule::unique('pgsql_second.personal_montaje', 'codigo_qr')],
            'autorizado' => 'required|boolean',
            'motivo_bloqueo' => 'nullable|string|max:255',
        ]);

        DB::connection($this->connection)->table('personal_montaje')->insert([
            'documento' => $request->documento,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'cargo' => $request->cargo,
            'ruc_empresa' => $request->ruc_empresa,
            'nombre_empresa' => $request->nombre_empresa,
            'codigo_qr' => $request->codigo_qr,
            'autorizado' => $request->autorizado,
            'motivo_bloqueo' => $request->autorizado ? null : $request->motivo_bloqueo,
            'estado_presencia' => 'Afuera',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'documento' => 'required|string|max:15',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'nullable|email|max:255',
            'cargo' => 'nullable|string|max:100',
            'ruc_empresa' => 'required|string|max:11',
            'nombre_empresa' => 'required|string|max:255',
            'codigo_qr' => 'required|string|max:100|unique:pgsql_second.personal_montaje,codigo_qr,' . $id,
            'autorizado' => 'required|boolean',
            'motivo_bloqueo' => 'nullable|string|max:255',
        ]);

        DB::connection($this->connection)->table('personal_montaje')->where('id', $id)->update([
            'documento' => $request->documento,
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'cargo' => $request->cargo,
            'ruc_empresa' => $request->ruc_empresa,
            'nombre_empresa' => $request->nombre_empresa,
            'codigo_qr' => $request->codigo_qr,
            'autorizado' => $request->autorizado,
            'motivo_bloqueo' => $request->autorizado ? null : $request->motivo_bloqueo,
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->back();
    }

    public function historialEspecifico($id)
    {
        // Buscamos los logs de la tabla historial_accesos para este personal_id
        $logs = DB::connection($this->connection)
            ->table('historial_accesos')
            ->where('personal_id', $id)
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return response()->json($logs);
    }


    public function destroy($id)
    {
        // Ya no borramos el historial, solo marcamos al trabajador como eliminado
        DB::connection($this->connection)
            ->table('personal_montaje')
            ->where('id', $id)
            ->update([
                'deleted_at' => Carbon::now(),
                'autorizado' => false, // Opcional: le quitamos el permiso por seguridad
                'updated_at' => Carbon::now(),
            ]);

        return redirect()->back();
    }
    // public function validar(Request $request)
    // {
    //     // Validamos que llegue el campo 'documento'
    //     $request->validate(['documento' => 'required|string']);
    //     $documento = $request->documento;

    //     // Caso Maestro (Pase de Emergencia)
    //     if ($documento === 'PROX26-MASTER') {
    //         return response()->json([
    //             'status' => 'success',
    //             'color' => 'bg-fuchsia-600',
    //             'titulo' => 'ACCESO MAESTRO',
    //             'mensaje' => 'Pase de Emergencia Autorizado.',
    //             'persona' => ['nombres' => 'ADMIN', 'apellidos' => 'MASTER', 'nombre_empresa' => 'ORGANIZACIÓN', 'documento' => 'MASTER']
    //         ]);
    //     }

    //     // Buscamos a la persona por DNI o por Código QR
    //     $persona = DB::connection($this->connection)
    //         ->table('personal_montaje')
    //         ->where('documento', $documento)
    //         ->orWhere('codigo_qr', $documento)
    //         ->first();

    //     if (!$persona) {
    //         return response()->json([
    //             'status' => 'error',
    //             'color' => 'bg-red-600',
    //             'titulo' => 'NO ENCONTRADO',
    //             'mensaje' => 'El documento o código no existe en el sistema.',
    //         ]);
    //     }

    //     if (!$persona->autorizado) {
    //         return response()->json([
    //             'status' => 'error',
    //             'color' => 'bg-slate-800', // Este es el único plomo/oscuro intencional
    //             'titulo' => 'ACCESO BLOQUEADO',
    //             'mensaje' => $persona->motivo_bloqueo ?? 'No autorizado por seguridad.',
    //             'persona' => $persona
    //         ]);
    //     }

    //     // Lógica de Toggle Adentro/Afuera (Manejando NULL para usuarios nuevos)
    //     $esEntrada = in_array($persona->estado_presencia, ['Afuera', null, '']);
    //     $nuevoEstado = $esEntrada ? 'Adentro' : 'Afuera';

    //     DB::connection($this->connection)->table('personal_montaje')->where('id', $persona->id)->update([
    //         'estado_presencia' => $nuevoEstado,
    //         'updated_at' => \Carbon\Carbon::now()
    //     ]);

    //     return response()->json([
    //         'status' => 'success',
    //         'color' => $esEntrada ? 'bg-emerald-600' : 'bg-blue-600',
    //         'titulo' => $esEntrada ? 'PASE CORRECTO' : 'SALIDA REGISTRADA',
    //         'mensaje' => $esEntrada ? 'Bienvenido al recinto.' : 'Regrese pronto.',
    //         'persona' => $persona
    //     ]);
    // }


    public function validar(Request $request)
    {
        // 1. Validamos la entrada
        $request->validate(['documento' => 'required|string']);
        $documento = $request->documento;

        // Caso Maestro (Pase de Emergencia)
        if ($documento === 'PROX26-MASTER') {
            return response()->json([
                'status' => 'success',
                'color' => 'bg-fuchsia-600',
                'titulo' => 'ACCESO MAESTRO',
                'mensaje' => 'Pase de Emergencia Autorizado.',
                'persona' => ['nombres' => 'ADMIN', 'apellidos' => 'MASTER', 'nombre_empresa' => 'ORGANIZACIÓN', 'documento' => 'MASTER']
            ]);
        }

        // 2. Buscamos a la persona
        $persona = DB::connection($this->connection)
            ->table('personal_montaje')
            ->where(function ($query) use ($documento) {
                $query->where('documento', $documento)
                    ->orWhere('codigo_qr', $documento);
            })
            ->whereNull('deleted_at') // <--- Solo si no está borrado
            ->first();

        if (!$persona) {
            return response()->json([
                'status' => 'error',
                'color' => 'bg-red-600',
                'titulo' => 'NO ENCONTRADO',
                'mensaje' => 'El documento o código no existe en el sistema.',
            ]);
        }

        // 3. Verificamos autorización
        if (!$persona->autorizado) {
            return response()->json([
                'status' => 'error',
                'color' => 'bg-slate-800',
                'titulo' => 'ACCESO BLOQUEADO',
                'mensaje' => $persona->motivo_bloqueo ?? 'No autorizado por seguridad.',
                'persona' => $persona
            ]);
        }

        // 4. Lógica de Historial e Intercambio de Estado
        $esEntrada = in_array($persona->estado_presencia, ['Afuera', null, '']);
        $nuevoEstado = $esEntrada ? 'Adentro' : 'Afuera';
        $movimiento = $esEntrada ? 'INGRESO' : 'SALIDA';

        // Iniciamos una transacción para asegurar que ambos inserts ocurran o ninguno
        DB::connection($this->connection)->transaction(function () use ($persona, $nuevoEstado, $movimiento) {
            // Actualizar estado en tabla principal
            DB::connection($this->connection)
                ->table('personal_montaje')
                ->where('id', $persona->id)
                ->update([
                    'estado_presencia' => $nuevoEstado,
                    'updated_at' => Carbon::now()
                ]);

            // Insertar en historial_accesos (Nombres de columnas según tu imagen)
            DB::connection($this->connection)
                ->table('historial_accesos')
                ->insert([
                    'personal_id'      => $persona->id,
                    'tipo_movimiento'  => $movimiento,
                    'fecha_hora'       => Carbon::now(),
                    'puerta_acceso'    => 'SCANNER_PRINCIPAL',
                    'usuario_seguridad' => auth()->user()->name ?? 'SISTEMA_AUTO',
                    'acceso_concedido' => true,
                    'notas'            => 'Validación mediante terminal de escaneo'
                ]);
        });

        return response()->json([
            'status' => 'success',
            'color' => $esEntrada ? 'bg-emerald-600' : 'bg-blue-600',
            'titulo' => $esEntrada ? 'PASE CORRECTO' : 'SALIDA REGISTRADA',
            'mensaje' => $esEntrada ? 'Bienvenido al recinto.' : 'Regrese pronto.',
            'persona' => $persona
        ]);
    }

    public function importar(Request $request)
    {
        // 1. Validación estricta de extensiones (Solo Excel o CSV)
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ], [
            'archivo.mimes' => 'El archivo debe ser un Excel (.xlsx, .xls) o CSV.',
        ]);

        try {
            // Ejecutamos la importación
            Excel::import(new \App\Imports\PersonalMontajeImport, $request->file('archivo'));

            return redirect()->back()->with('success', 'Personal importado correctamente.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // 2. Si el Excel no cumple la estructura (ej. falta un DNI o nombre en alguna fila)
            $fallos = $e->failures();
            $mensajeError = 'Error en la fila ' . $fallos[0]->row() . ': ' . $fallos[0]->errors()[0];

            return redirect()->back()->withErrors(['error_importacion' => $mensajeError]);
        } catch (\Exception $e) {
            // 3. Si el archivo está corrupto o hay un error de código
            return redirect()->back()->withErrors(['error_importacion' => 'El archivo está vacío o no tiene el formato correcto.']);
        }
    }


    // public function togglePresencia(Request $request, $id)
    // {
    //     // El dd() rompe a Inertia, por eso lo quitamos

    //     $request->validate([
    //         'estado_presencia' => 'required|in:Adentro,Afuera' // <-- Corregido a Afuera
    //     ]);

    //     DB::connection($this->connection)
    //         ->table('personal_montaje')
    //         ->where('id', $id)
    //         ->update([
    //             'estado_presencia' => $request->estado_presencia,
    //             'updated_at' => Carbon::now(),
    //         ]);

    //     return back();
    // }

    public function togglePresencia(Request $request, $id)
    {
        $request->validate([
            'estado_presencia' => 'required|in:Adentro,Afuera'
        ]);

        $nuevoEstado = $request->estado_presencia;
        $movimiento = ($nuevoEstado === 'Adentro') ? 'INGRESO' : 'SALIDA';

        DB::connection($this->connection)->transaction(function () use ($id, $nuevoEstado, $movimiento) {
            // 1. Actualizar tabla principal
            DB::connection($this->connection)
                ->table('personal_montaje')
                ->where('id', $id)
                ->update([
                    'estado_presencia' => $nuevoEstado,
                    'updated_at' => Carbon::now(),
                ]);

            // 2. Registrar en historial
            DB::connection($this->connection)
                ->table('historial_accesos')
                ->insert([
                    'personal_id'      => $id,
                    'tipo_movimiento'  => $movimiento,
                    'fecha_hora'       => Carbon::now(),
                    'puerta_acceso'    => 'CONTROL_MANUAL',
                    'usuario_seguridad' => auth()->user()->name ?? 'ADMIN',
                    'acceso_concedido' => true,
                    'notas'            => 'Cambio de estado forzado desde el panel de administración'
                ]);
        });

        return back();
    }
}
