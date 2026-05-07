<?php

namespace App\Contracts;

interface ExchanceRateProviderInterface
{
    public function convertToBRL(float $amount, string $currency): float;
}