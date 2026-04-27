<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;

class MailInscripcion extends Mailable
{
    use Queueable, SerializesModels;

    public $persona;
    public $empresa;

    public function __construct($inscripcion, $pago)
    {
        $this->inscripcion = $inscripcion;
        $this->pago = $pago;
    }

    public function envelope(): Envelope
    {
        if (\App::environment('local')) {
            if (strlen($this->inscripcion->facturacion->correo_facturador) > 0) {
                return new Envelope(
                    from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - ProExplo 2026 Confirmación de Inscripción",
                    //   cc: ['ext_analistaprogramador2@iimp.org.pe']
                    cc: ['inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                    bcc: ['wmc.itsupport@iimp.org.pe', 'john.moron@iimp.org.pe']
                );
            } else {
                return new Envelope(
                    from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - ProExplo 2026 Confirmación de Inscripción",
                    //  cc: ['ext_analistaprogramador2@iimp.org.pe']
                    cc: ['inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                    bcc: ['wmc.itsupport@iimp.org.pe', 'john.moron@iimp.org.pe']
                );
            }
        } else {
            if (strlen($this->inscripcion->facturacion->correo_facturador) > 0) {
                return new Envelope(
                    from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - ProExplo 2026 Confirmación de Inscripción",
                    //  cc: [ 'ext_analistaprogramador2@iimp.org.pe']
                    cc: ['inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                    bcc: ['wmc.itsupport@iimp.org.pe', 'john.moron@iimp.org.pe']
                );
            } else {
                return new Envelope(
                    from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - ProExplo 2026 Confirmación de Inscripción",
                    //  cc: [ 'ext_analistaprogramador2@iimp.org.pe']
                    cc: ['inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                    bcc: ['wmc.itsupport@iimp.org.pe', 'john.moron@iimp.org.pe']
                );
            }
        }
    }

    public function content(): Content
    {

        return new Content(
            view: 'emails.es.confirmacion_inscripcion',
            with: [
                'qr_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . $this->inscripcion->qr,
                'inscripcion' => $this->inscripcion,
                'pago' => $this->pago,
            ],
        );
    }


    // public function attachments(): array
    // {
    //     // 1. Verificar si la categoría requiere documento
    //     if ($this->inscripcion->categoria_inscripcion->requiere_documento) {

    //         $path = $this->inscripcion->document_path;

    //         // 2. Extraer solo el nombre del archivo de forma segura
    //         $file_name = basename($path);
    //         $full_path = storage_path('app/public/documents/' . $file_name);

    //         // 3. Validar si el archivo existe y NO es un directorio
    //         if ($file_name != "" && file_exists($full_path) && !is_dir($full_path)) {
    //             return [
    //                 Attachment::fromPath($full_path)
    //                     ->as($file_name) // Nombre con el que se verá en el correo
    //                     ->withMime($this->inscripcion->categoria_inscripcion->document_type),
    //             ];
    //         }
    //     }

    //     return [];
    // }


    public function attachments(): array
    {
        $adjuntos = [];

        // ==========================================
        // 1. LÓGICA DEL DOCUMENTO DINÁMICO
        // ==========================================
        if ($this->inscripcion->categoria_inscripcion->requiere_documento) {
            $path = $this->inscripcion->document_path;
            $file_name = basename($path);
            $full_path = storage_path('app/public/documents/' . $file_name);

            if ($file_name != "" && file_exists($full_path) && !is_dir($full_path)) {
                $adjuntos[] = Attachment::fromPath($full_path)
                    ->as($file_name)
                    ->withMime($this->inscripcion->categoria_inscripcion->document_type ?? 'application/pdf');
            }
        }

        // ==========================================
        // 2. LÓGICA DEL DOCUMENTO ESTÁTICO (Términos)
        // ==========================================

        // Detectamos el idioma actual
        $idioma = app()->getLocale();

        // Definimos el nombre exacto del archivo según el idioma
        $file_name_estatico = $idioma === 'en' ? 'terminos_en.pdf' : 'terminos_es.pdf';
        $nombre_para_correo = $idioma === 'en' ? 'Terms_and_Conditions.pdf' : 'Terminos_y_Condiciones.pdf';

        // Ruta completa: public/documents/terminos_es.pdf (o en.pdf)
        $full_path_estatico = public_path('documents/' . $file_name_estatico);

        // Validar si el archivo existe y NO es un directorio (Tal cual tu lógica)
        if ($file_name_estatico != "" && file_exists($full_path_estatico) && !is_dir($full_path_estatico)) {
            $adjuntos[] = Attachment::fromPath($full_path_estatico)
                ->as($nombre_para_correo) // Nombre que verá el usuario
                ->withMime('application/pdf');
        } else {
            // Por si el archivo estático no está, te avisa en el log
            Log::error("No se encontró el documento estático en la ruta: " . $full_path_estatico);
        }

        // dd($adjuntos); // Descomenta esta línea solo si necesitas probar qué se está enviando

        return $adjuntos;
    }
}
