<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class Amount
{
    public function __construct(
       public float $value,
       public string $currencyCode,
    ) {
    }
}