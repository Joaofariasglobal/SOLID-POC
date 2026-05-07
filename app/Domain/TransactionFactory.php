<?php

namespace App\Domain;

use App\Contracts\TransactionFactoryInterface;
use InvalidArgumentException;

class TransactionFactory implements TransactionFactoryInterface{
    public function createTransaction(array $data): BaseTransaction{
        $type = $data["type"] ?? null;
        $amount = (float)$data["amount"];
        $description = (string) ($data["description"] ?? "");
        $category = (string) $data["category"];

        if ($type === "income"){
            return new IncomeTransaction($amount, $description, $category);
        }
        elseif ($type === "expense"){
            return new ExpenseTransaction($amount, $description, $category);
        }
        else{
            throw new InvalidArgumentException("Tipo '{$type}' inválido");
        }
    }
}