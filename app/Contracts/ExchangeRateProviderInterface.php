<?php

namespace App\Contracts;

interface ExchangeRateProviderInterface
{
    public function convertToBRL(float $amount, string $currency): float;
}