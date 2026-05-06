<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class CreateUserService
{            
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function createUser(array $data): array
    {
        if (! isset($data['name']) || trim($data['name']) === '') {
            throw new InvalidArgumentException('Nome é obrigatório.');
        }

        if (! isset($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        if (strlen($data['name']) < 3) {
            throw new InvalidArgumentException('Nome deve ter ao menos 3 caracteres.');
        }

        $userRepository = $this->userRepository->createUser($data);

        Log::info("[FinanceService] Usuário criado: id={$userRepository['id']} email={$data['email']}");

        try {
            Mail::raw("Bem-vindo(a), {$data['name']}!", function ($m) use ($data) {
                $m->to($data['email'])->subject('Cadastro realizado');
            });
        } catch (\Throwable $e) {
            Log::warning("[FinanceService] Falha ao enviar e-mail: {$e->getMessage()}");
        }

        return $userRepository;
    }
}