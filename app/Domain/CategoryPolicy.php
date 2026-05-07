<?php

namespace App\Domain;

class CategoryPolicy
{
    public static function validateCategory(string $type, string $category): bool
    {
        $categories = config('categories.' . $type, []);
        return in_array($category, $categories, true);
    }

    public static function getCategoriesByType(string $type): array
    {
        return config('categories.' . $type, []);
    }

    public static function validateCategoryWithType(string $type, string $category): bool
    {
        return self::validateCategory($type, $category);
    }
}