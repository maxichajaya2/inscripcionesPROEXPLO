<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

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
            return new Envelope(
                from: new Address('extemin@iimp.org.pe', config('app.event_name')),
                subject: config('app.event_name') . " - Confirmación de inscripción",
                cc: ['sipionibanez.d@gmail.com',$this->inscripcion->facturacion->correo_facturador],
                bcc: ['diego.sipion@iimp.org.pe']
            );

        }else{
            return new Envelope(
                from: new Address('extemin@iimp.org.pe', config('app.event_name')),
                subject: config('app.event_name') . " - Confirmación de inscripción",
                cc: [ 'inscripciones@iimp.org.pe', 'cobranzas@iimp.org.pe', $this->inscripcion->facturacion->correo_facturador],
                bcc: ['diego.sipion@iimp.org.pe','sara.leyton@iimp.org.pe']
            );
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
}
