<?php

namespace App\Services\Currency;

use App\Contracts\ExchangeRateProviderInterface;
use InvalidArgumentException;

class StaticRateExchangeRateProvider implements ExchangeRateProviderInterface
{
    private const EXCHANGE_RATES = [
        'USD' => 5.20,
        'EUR' => 5.65,
        'BRL' => 1.00,
    ];

    public function convertToBrl(float $amount, string $fromCurrency): float
    {
        if (!isset(self::EXCHANGE_RATES[$fromCurrency])) {
            throw new InvalidArgumentException('Moeda não suportada.');
        }

        return $amount * self::EXCHANGE_RATES[$fromCurrency];
    }
}