<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

class MailContacto extends Mailable
{
    use Queueable, SerializesModels;

    // Esta variable contendrá todos los datos del formulario (nombres, email, etc.)
    public $contacto;

    public function __construct($contacto)
    {
        $this->contacto = $contacto;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
            subject: config('app.event_name') . " - ProExplo 2026 -  ",
            cc: ['inscripciones@iimp.org.pe', $this->contacto->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.es.contacto',
            with: [
                'contacto' => $this->contacto,
            ],
        );
    }

    public function attachments(): array
    {
        // El formulario de contacto simple no suele llevar adjuntos,
        // así que lo dejamos vacío.
        return [];
    }
}
