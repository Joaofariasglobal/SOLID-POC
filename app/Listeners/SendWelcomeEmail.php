<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event) : void
    {
        Mail::raw("Bem-vindo ao nosso sistema, {$event->email}!", function ($message) use ($event) {
            $message->to($event->email)->subject('Bem-vindo!');
        });
    }
}
