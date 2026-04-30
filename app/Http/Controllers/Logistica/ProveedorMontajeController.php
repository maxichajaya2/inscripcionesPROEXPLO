<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\MensajeProveedor; // Asegúrate de tener este Mailable creado
use Illuminate\Support\Facades\Log;
use App\Models\ProveedorMontaje;

class ProveedorMontajeController extends Controller
{
    public function index()
    {
        // Eloquent ya sabe usar pgsql_second por el modelo
        $proveedores = ProveedorMontaje::orderBy('id', 'desc')->get();
        return Inertia::render('Logistica/ProveedorMontaje/Index', [
            'proveedores' => $proveedores
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            // FIX: Forzamos la conexión pgsql_second para la regla unique
            'email_principal' => 'required|email|unique:pgsql_second.proveedores,email_principal',
            'emails_cc' => 'nullable|array',
            'emails_cc.*' => 'email'
        ]);

        ProveedorMontaje::create([
            'nombre_empresa' => $request->nombre_empresa,
            'email_principal' => $request->email_principal,
            'emails_cc' => $request->emails_cc,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Proveedor creado con éxito.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            // FIX: Forzamos pgsql_second e ignoramos el ID del proveedor actual
            'email_principal' => 'required|email|unique:pgsql_second.proveedores,email_principal,' . $proveedor->id,
            'emails_cc' => 'nullable|array',
            'emails_cc.*' => 'email',
            'is_active' => 'boolean'
        ]);

        $proveedor->update([
            'nombre_empresa' => $request->nombre_empresa,
            'email_principal' => $request->email_principal,
            'emails_cc' => $request->emails_cc,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->back()->with('success', 'Proveedor eliminado con éxito.');
    }

    public function enviarMasivo(Request $request)
    {
        // 1. Validar la petición (asegúrate de quitar el dd() que tenías)
        $request->validate([
            'proveedores_ids' => 'required|array',
            'proveedores_ids.*' => 'exists:pgsql_second.proveedores,id'
        ]);

        // 2. Obtener proveedores activos
        $proveedores = ProveedorMontaje::whereIn('id', $request->proveedores_ids)
            ->where('is_active', true)
            ->get();

        $enviados = 0;
        $errores = 0;

        // Log: Inicio del proceso
        Log::info("🚀 INICIO ENVÍO MASIVO: Intentando enviar a " . $proveedores->count() . " proveedores.");

        // 3. Bucle de envío
        foreach ($proveedores as $proveedor) {
            try {
                Mail::mailer('proveedores')
                    ->to($proveedor->email_principal)
                    ->cc($proveedor->emails_cc ?? [])
                    ->send(new \App\Mail\MailProveedorMontaje($proveedor));

                // Actualizar fecha solo si no hubo error
                $proveedor->update(['last_sent_at' => now()]);
                $enviados++;

                // Log: Éxito individual (Opcional, pero útil para rastrear correos específicos)
                Log::info("✅ Enviado: {$proveedor->email_principal}");
            } catch (Exception $e) {
                $errores++;
                // Log: Error detallado si algo falla, pero el bucle CONTINÚA
                Log::error("❌ Error enviando a {$proveedor->email_principal} - Motivo: " . $e->getMessage());
            }
        }

        // Log: Resumen final
        Log::info("🏁 FIN ENVÍO MASIVO: {$enviados} exitosos, {$errores} fallidos.");

        // 4. Retornar mensaje al frontend
        if ($errores > 0) {
            // Si tienes configurado Inertia para recibir 'warning' o si solo usas 'success'/'error'
            return redirect()->back()->with('success', "Se enviaron {$enviados} correos, pero hubo {$errores} errores. Revisa los logs.");
        }

        return redirect()->back()->with('success', "Se enviaron correos con éxito a {$enviados} proveedores.");
    }
}
