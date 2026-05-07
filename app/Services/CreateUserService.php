<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Events\UserRegistered;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CreateUserService
{            
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Dispatcher $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function createUser(array $data): array
    {
        if (! isset($data['name']) || trim($data['name']) === '') {
            throw new InvalidArgumentException('Nome é obrigatório.');
        }

        if (strlen($data['name']) < 3) {
            throw new InvalidArgumentException('Nome deve ter ao menos 3 caracteres.');
        }

        if (! isset($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }      

        $user = $this->userRepository->createUser($data);

        $this->logger->info("[Users] Usuário criado: id={$user['id']} email={$user['email']}");

        $this->eventDispatcher->dispatch(new UserRegistered(
            (int) $user['id'],
            $data['name'],
            $data['email']
        ));

        return $user;
    }
}