<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class DetailsRequest
{
    public ?string $sign = null;

    public function __construct(
        public string $orderId, // @see TransactionResponse::id
    ) {
    }
}