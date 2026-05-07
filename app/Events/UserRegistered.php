<?php

namespace App\Events;

class UserRegistered
{
    public function __construct(public readonly int $userId, public readonly string $name, public readonly string $email)
    {
    }
}