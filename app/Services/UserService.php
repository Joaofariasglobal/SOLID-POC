<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use InvalidArgumentException;

class UserService implements UserServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
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

        return $this->userRepository->createUser($data);
    }

    public function findUser(int $userId): ?array
    {
        return $this->userRepository->findUser($userId);
    }
}