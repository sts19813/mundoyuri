<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        if (! filled($event->user->email)) {
            return;
        }

        try {
            Mail::to($event->user->email)->send(new WelcomeMail($event->user));
        } catch (Throwable $exception) {
            // Un fallo temporal del proveedor de correo no debe impedir el registro.
            report($exception);
        }
    }
}
