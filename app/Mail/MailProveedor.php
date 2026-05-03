<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\Proveedor; // <-- No olvides importar tu modelo
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;

class MailProveedor extends Mailable
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

            subject: "Comunicación urgente a proveedores – Etapa Montaje - PROEXPLO 2026"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.es.proveedor',
            with: [
                'proveedor' => $this->proveedor, // Se lo pasamos a la vista
            ],
        );
    }

    public function attachments(): array
    {
        // 1. El nombre exacto del archivo
        $file_name = 'proveedores.xlsx';

        // 2. Ruta completa: public/documents/proveedores.xlsx
        $full_path = public_path('documents/' . $file_name);

        // 3. Validar si el archivo existe y NO es un directorio (Tal cual tu lógica)
        if ($file_name != "" && file_exists($full_path) && !is_dir($full_path)) {
            return [
                Attachment::fromPath($full_path)
                    ->as('ANEXO_07_REGISTRO_DE_INDUCCIONES.xlsx') // Nombre que verá el proveedor
                    ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ];
        }

        // Por si el archivo se mueve, te avisa en el log
        Log::error("No se encontró el archivo adjunto en la ruta: " . $full_path);

        return [];
    }
}
