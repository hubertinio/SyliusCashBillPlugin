<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class Amount
{
    public function __construct(
       public float $value,
       public string $currencyCode,
    ) {
        $this->currencyCode = mb_strtoupper($this->currencyCode);
    }

    public static function createFromInt(int $total, string $currencyCode): self
    {
        $total = self::calcTotal($total);

        return new self($total, $currencyCode);
    }

    public static function calcTotal(int $total): float
    {
        return $total / 100;
    }
}