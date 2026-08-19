<?php

namespace App\Mail;

use App\Models\Episode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EpisodeAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Episode $episode) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Nuevo episodio disponible! '.$this->episode->title.' · '.$this->episode->series->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.episodes.available');
    }
}
