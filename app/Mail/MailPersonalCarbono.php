<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\PersonalCarbono;

class MailPersonalCarbono extends Mailable
{
    use Queueable, SerializesModels;

    public $personal;

    // Recibimos la instancia de PersonalCarbono desde el controlador
    public function __construct(PersonalCarbono $personal)
    {
        $this->personal = $personal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('lesly.alvarado@iimp.org.pe', config('app.event_name')),
            // Puedes ajustar el asunto a lo que corresponda
            //  bcc: ['lesly.alvarado@iimp.org.pe'],
            subject: "Sé parte de un PROEXPLO 2026 carbono neutro"
        );
    }

    public function content(): Content
    {
        return new Content(
            // Asegúrate de crear este archivo en resources/views/emails/es/personal_carbono.blade.php
            view: 'emails.es.personal_carbono',
            with: [
                'personal' => $this->personal,
            ],
        );
    }

    public function attachments(): array
    {
        // Se deja en blanco para no adjuntar ningún documento
        return [];
    }
}
