<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\PersonalCarbono;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PersonalCarbonoImport;
// Asegúrate de tener un Mailable para esto, por ejemplo:
// use App\Mail\MailPersonalCarbono;

class PersonalCarbonoController extends Controller
{
    public function index()
    {
        $personal = PersonalCarbono::orderBy('id', 'desc')->get();

        // Asegúrate de ajustar la ruta de tu vista de Inertia según corresponda
        return Inertia::render('Logistica/PersonalCarbono/Index', [
            'personal' => $personal
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            // Forzamos la conexión pgsql_second y apuntamos a la tabla correcta
            'correo' => 'required|email|unique:pgsql_second.personal_carbono,correo',
        ]);

        PersonalCarbono::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
        ]);

        return redirect()->back()->with('success', 'Registro creado con éxito.');
    }

    public function update(Request $request, PersonalCarbono $personal)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            // Ignoramos el ID del registro actual para la validación unique
            'correo' => 'required|email|unique:pgsql_second.personal_carbono,correo,' . $personal->id,
        ]);

        $personal->update([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
        ]);

        return redirect()->back()->with('success', 'Registro actualizado con éxito.');
    }

    public function destroy(PersonalCarbono $personal)
    {
        $personal->delete();
        return redirect()->back()->with('success', 'Registro eliminado con éxito.');
    }

    public function enviarMasivo(Request $request)
    {
        // 1. Validar la petición
        $request->validate([
            'personal_ids' => 'required|array',
            'personal_ids.*' => 'exists:pgsql_second.personal_carbono,id'
        ]);

        // 2. Obtener los registros seleccionados
        $listaPersonal = PersonalCarbono::whereIn('id', $request->personal_ids)->get();

        $enviados = 0;
        $errores = 0;

        // Log: Inicio del proceso
        Log::info("🚀 INICIO ENVÍO MASIVO: Intentando enviar a " . $listaPersonal->count() . " personas.");

        // 3. Bucle de envío
        foreach ($listaPersonal as $persona) {
            try {
                // Aquí debes reemplazar MailPersonalCarbono con el Mailable que vayas a usar
                Mail::mailer('proveedores') // Asegúrate de que el mailer sea el correcto
                    ->to($persona->correo)
                    ->send(new \App\Mail\MailPersonalCarbono($persona));

                $enviados++;

                // 👇 ACTUALIZAMOS EL ESTADO A TRUE Y GUARDAMOS 👇
                $persona->enviado = true;
                $persona->save();

                // Log: Éxito individual
                Log::info("✅ Enviado y actualizado en BD: {$persona->correo}");
            } catch (\Exception $e) {
                $errores++;
                // Log: Error detallado
                Log::error("❌ Error enviando a {$persona->correo} - Motivo: " . $e->getMessage());
            }
        }

        // Log: Resumen final
        Log::info("🏁 FIN ENVÍO MASIVO: {$enviados} exitosos, {$errores} fallidos.");

        // 4. Retornar mensaje al frontend
        if ($errores > 0) {
            return redirect()->back()->with('success', "Se enviaron {$enviados} correos, pero hubo {$errores} errores. Revisa los logs.");
        }

        return redirect()->back()->with('success', "Se enviaron correos con éxito a {$enviados} personas y se actualizó su estado.");
    }

    public function importarExcel(Request $request)
    {
        // Solo validamos que sea un archivo y que no pese más de 5MB
        $request->validate([
            'archivo_excel' => 'required|file|max:5120',
        ]);

        try {
            Excel::import(new \App\Imports\PersonalCarbonoImport, $request->file('archivo_excel'));
            return redirect()->back()->with('success', 'Registros importados exitosamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error importando Excel: " . $e->getMessage());
            return redirect()->back()->withErrors(['archivo_excel' => 'Error: ' . $e->getMessage()]);
        }
    }
}
