<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

class MailContrato extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Get the message content definition.
     */
    public function envelope(): Envelope
    {
        if(\App::environment('local')){
            return new Envelope(
                from: new Address('extemin@iimp.org.pe', config('app.event_name')),
                subject: config('app.event_name')." Convención Minera - Confirmación de reserva de stands",
                cc: ['sipionibanez.d@gmail.com'],
                bcc: ['diego.sipion@iimp.org.pe']
            );

        }else{
            return new Envelope(
                from: new Address('extemin@iimp.org.pe', config('app.event_name')),
                subject: config('app.event_name')." Convención Minera - Confirmación de reserva de stands",
                cc: ['extemin@iimp.org.pe'],
                bcc: ['diego.sipion@iimp.org.pe','sara.leyton@iimp.org.pe','lourdes.alaya@iimp.org.pe','ext_extemin01@iimp.org.pe']
            );
        }

    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.es.contrato',
            with: [
                'data' => $this->data
            ],
        );
    }
}
