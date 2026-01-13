<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

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
         if(\App::environment('local')){
            if( strlen($this->inscripcion->facturacion->correo_facturador) > 0){
                return new Envelope(
                    from: new Address('WMC2026authors@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - Confirmación de inscripción",
                    // cc: [ 'inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                     cc: ['ext_analistaprogramador2@iimp.org.pe'],
                    // bcc: ['ext_analistaprogramador2@iimp.org.pe']
                );
            }else{
                return new Envelope(
                    from: new Address('WMC2026authors@iimp.org.pe', config('app.event_name')),
                    subject: config('app.event_name') . " - Confirmación de inscripción",
                    // cc: [ 'inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe'],
                    // bcc: ['ext_analistaprogramador2@iimp.org.pe']
                );
            }
        }else{
            // if( strlen($this->inscripcion->facturacion->correo_facturador) > 0){
            //     return new Envelope(
            //         from: new Address('WMC2026authors@iimp.org.pe', config('app.event_name')),
            //         subject: config('app.event_name') . " - Confirmación de inscripción",
            //         cc: [ 'inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
            //         bcc: ['diego.sipion@iimp.org.pe','sara.leyton@iimp.org.pe']
            //     );
            // }else{
            //     return new Envelope(
            //         from: new Address('WMC2026authors@iimp.org.pe', config('app.event_name')),
            //         subject: config('app.event_name') . " - Confirmación de inscripción",
            //         cc: [ 'inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe'],
            //         bcc: ['diego.sipion@iimp.org.pe','sara.leyton@iimp.org.pe']
            //     );
            // }

        }
    }

    public function content(): Content
    {

            return new Content(
                view: 'emails.es.confirmacion_inscripcion',
                with: [
                    'inscripcion' => $this->inscripcion,
                    'pago' => $this->pago,
                ],
            );

    }

    public function attachments(): array
    {
        if($this->inscripcion->categoria_inscripcion->requiere_documento){

            $file_name = $this->inscripcion->document_path;
            $file_name = explode("/", $file_name);
            $file_name = $file_name[(sizeof($file_name) -1)] ;

            $file = file_get_contents( storage_path('app/public/documents'). "/".$file_name );

            return [
                Attachment::fromPath( storage_path('app/public/documents'). "/".$file_name )
                    ->withMime($this->inscripcion->categoria_inscripcion->document_type),
            ];

        }else{
            return [];
        }
    }
}
