<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;


class MailCredenciales extends Mailable
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
            view: 'emails.es.confirmacion_credenciales',
            with: [
                'qr_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . $this->inscripcion->qr,
                'inscripcion' => $this->inscripcion,
                'pago' => $this->pago,
            ],
        );
    }

    public function attachments(): array
    {
        // Al retornar un arreglo vacío, no se enviará ningún adjunto
        return [];
    }
}
