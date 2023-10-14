<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;

final class CashBillBridge implements CashBillBridgeInterface
{

    public function setAuthorizationData(string $environment, string $appId, string $appSecret): void
    {
        throw new \Exception("@TODO " . __METHOD__ . print_r(func_get_args(), true));
    }

    public function create(array $order): ?TransactionResponse
    {
        throw new \Exception("@TODO " . __METHOD__ . print_r(func_get_args(), true));
    }

    public function retrieve(string $orderId): TransactionResponse
    {
        throw new \Exception("@TODO " . __METHOD__ . print_r(func_get_args(), true));
    }

    public function consumeNotification($data): ?TransactionResponse
    {
        throw new \Exception("@TODO " . __METHOD__ . print_r(func_get_args(), true));
    }
}