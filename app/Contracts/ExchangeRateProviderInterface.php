<?php

namespace App\Contracts;

interface ExchangeRateProviderInterface
{
    public function convertToBrl(float $amount, string $fromCurrency): float;
}