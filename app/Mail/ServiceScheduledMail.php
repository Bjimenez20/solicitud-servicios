<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceRequest $serviceRequest)
    {
        Carbon::setLocale('es');
        $this->serviceRequest->loadMissing(['client', 'provider.user', 'installer', 'items.product', 'creator']);
    }

    public function envelope(): Envelope
    {
        $typeLabel = ucfirst($this->serviceRequest->type ?? 'servicio');

        return new Envelope(
            subject: "Confirmación de {$typeLabel} #{$this->serviceRequest->id} - Fecha, Hora y Técnico Asignado",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-scheduled',
        );
    }
}
