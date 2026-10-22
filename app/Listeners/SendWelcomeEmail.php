<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        $this->send($event->user);
    }

    public function send(User $user): void
    {
        if (! filled($user->email) || ! $user->hasVerifiedEmail()) {
            return;
        }

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (Throwable $exception) {
            // Un fallo temporal del proveedor de correo no debe impedir el registro.
            report($exception);
        }
    }
}
