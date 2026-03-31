<?php

namespace App\Mail;

use App\Models\DocumentoDte;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DteReceptorMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public DocumentoDte $dte,
        public string $xml,
        public string $pdfPath,
        public array $urlsAcuse,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DTE enviado al receptor: ' . $this->dte->folio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dte-receptor',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->xml, 'DTE-' . $this->dte->folio . '.xml')
                ->withMime('application/xml'),
            Attachment::fromPath($this->pdfPath)
                ->as('DTE-' . $this->dte->folio . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
