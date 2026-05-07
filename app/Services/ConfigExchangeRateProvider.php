<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use InvalidArgumentException;

class ConfigExchangeRateProvider implements ExchangeRateProviderInterface
{
    public function __construct(private array $rates) {
    
    }

    public function convertToBRL(float $amount, string $currency): float
    {
        if (! isset($this->rates[$currency])) {
            throw new InvalidArgumentException("Moeda {$currency} não suportada.");
        }
        return $amount * (float) $this->rates[$currency];
    }
}