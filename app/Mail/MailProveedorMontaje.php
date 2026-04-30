<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Proveedor; // <-- No olvides importar tu modelo

class MailProveedorMontaje extends Mailable
{
    use Queueable, SerializesModels;

    public $proveedor;

    // Recibimos el proveedor desde el controlador
    public function __construct(Proveedor $proveedor)
    {
        $this->proveedor = $proveedor;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('lesly.alvarado@iimp.org.pe', config('app.event_name')),
            subject: "Reunión inicial para el montaje de stands – PROEXPLO 2026"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.es.proveedor_montaje',
            with: [
                'proveedor' => $this->proveedor, // Se lo pasamos a la vista
            ],
        );
    }

    public function attachments(): array
    {
        // Al retornar un arreglo vacío, no se enviará ningún adjunto
        return [];
    }
}
