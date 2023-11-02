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

    public function getValueAsCent(): float
    {
        return 100 * $this->value;
    }

    public static function createFromCent(int $total, string $currencyCode): self
    {
        return new self(self::convertToDecimal($total), $currencyCode);
    }

    public static function convertToDecimal(int $total): string
    {
        return sprintf('%0.2f', $total / 100);
    }
}