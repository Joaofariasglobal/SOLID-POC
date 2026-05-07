<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Mail\Mailer;
use Psr\Log\LoggerInterface;
use Throwable;

class SendWelcomeEmail
{
    public function __construct(
        private Mailer $mailer,
        private LoggerInterface $logger
    ) {
    }

    public function handle(UserRegistered $event): void
    {
        try {
            $this->mailer->raw(
                "Bem-vindo(a), {$event->name}!",
                fn ($m) => $m->to($event->email)->subject('Cadastro realizado')
            );
        } catch (Throwable $e) {
            $this->logger->warning("[Users] Falha ao enviar e-mail: {$e->getMessage()}");
        }
    }
}