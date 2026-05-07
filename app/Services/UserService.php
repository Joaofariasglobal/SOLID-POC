<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Event;
use App\Events\UserRegistered;
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
        $user = $this->userRepository->createUser($data);
    
        Event::dispatch(new UserRegistered(
            userId: $user['id'],
            name: $user['name'],
            email: $user['email'],
        ));
    
        return $user;
    }

    public function findUser(int $userId): ?array
    {
        return $this->userRepository->findUser($userId);
    }
}