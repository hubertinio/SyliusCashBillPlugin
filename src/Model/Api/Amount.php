<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class Amount
{
    public function __construct(
       public string $value,
       public string $currencyCode,
    ) {
        $this->currencyCode = mb_strtoupper($this->currencyCode);
    }

    public static function createFromInt(int $total, string $currencyCode): self
    {
        return new self(self::calcTotal($total), $currencyCode);
    }

    public static function calcTotal(int $total): string
    {
        return sprintf('%0.2f', $total / 100);
    }
}