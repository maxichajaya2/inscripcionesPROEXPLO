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
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('inscripciones@iimp.org.pe', config('app.event_name')),
            subject: config('app.event_name') . " - Nuevo mensaje de contacto",
            // Si quieres que el soporte vea a quién responder directamente:
            replyTo: [
                new Address($this->data['email'], $this->data['nombres'] . ' ' . $this->data['apellidos']),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            // Asegúrate de crear esta vista en resources/views/emails/contacto.blade.php
            view: 'emails.contacto',
            with: [
                'nombres'   => $this->data['nombres'],
                'apellidos' => $this->data['apellidos'],
                'email'     => $this->data['email'],
                'telefono'  => $this->data['telefono_completo'],
                'mensaje'   => $this->data['mensaje'],
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
