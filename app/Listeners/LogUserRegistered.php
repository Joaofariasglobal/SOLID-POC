<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Events\UserRegistered;

class LogUserRegistered
{
    public function handle(UserRegistered $event) : void
    {
        Log::info(
            "[UserRepository] Novo usuário registrado " . 
            "id={$event->userId} " .
            "email={$event->email}"
        );
    }
}