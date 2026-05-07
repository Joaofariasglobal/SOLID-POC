<?php

namespace App\Domain;

class CategoryPolicy
{
    private const ALLOWED = [
        'income' => ['salario', 'freelance', 'investimento', 'outros'],
        'expense' => ['alimentacao', 'transporte', 'moradia', 'lazer', 'saude', 'educacao', 'outros'],
    ];

    public function isAllowed(string $type, string $category): bool
    {
        return in_array($category, self::ALLOWED[$type] ?? [], true);
    }
}