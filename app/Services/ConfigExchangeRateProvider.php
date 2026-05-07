<?php

namespace App\Services;

use App\Contracts\ExchanceRateProviderInterface;
use InvalidArgumentException;

class ConfigExchanceRateProvider implements ExchanceRateProviderInterface
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