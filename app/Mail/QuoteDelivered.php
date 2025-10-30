<?php

namespace App\Mail;

use App\Models\Quote;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteDelivered extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $quote;
    public $company;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Quote $quote, $company, $pdfPath = null)
    {
        $this->quote = $quote;
        $this->company = $company;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quote #{$this->quote->quote_number} from {$this->company->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-delivered',
            with: [
                'quote' => $this->quote,
                'company' => $this->company,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfPath && file_exists(storage_path('app/public/' . $this->pdfPath))) {
            $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->pdfPath))
                ->as("quote-{$this->quote->quote_number}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
