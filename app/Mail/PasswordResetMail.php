<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña de Mundo Yuri',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.users.password-reset',
            with: [
                'resetUrl' => URL::to(route('password.reset', [
                    'token' => $this->token,
                    'email' => $this->user->getEmailForPasswordReset(),
                ], false)),
            ],
        );
    }
}
