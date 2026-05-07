<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserRegistered
{
    use Dispatchable;
    public int $userId;
    public string $name;
    public string $email;

    public function __construct(int $userId, string $name, string $email)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
    }
}