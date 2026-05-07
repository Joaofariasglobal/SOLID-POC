<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Domain\CategoryPolicy;

class ValidCategoryForTypeRule implements Rule
{
    private string $transactionType;

    public function __construct(string $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    public function passes($attribute, $value): bool
    {
        return CategoryPolicy::validateCategoryWithType($this->transactionType, $value);
    }

    public function message(): string
    {
        $validCategories = implode(', ', CategoryPolicy::getCategoriesByType($this->transactionType));
         return "A categoria deve ser uma de: {$validCategories}";
    }
}