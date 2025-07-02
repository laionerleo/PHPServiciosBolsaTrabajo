<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMessageMail extends Mailable
{
    use Queueable, SerializesModels;

        public string $mensaje;
    public string $asunto;
    /**
     * Create a new message instance.
     *
     * @return void
     */
      public function __construct(string $asunto, string $mensaje)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
    }
    
    
        /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }


    
    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'generic',
            with: ['mensaje' => $this->mensaje],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
