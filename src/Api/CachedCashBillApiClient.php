<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Api;

use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @see https://api.cashbill.pl/category/api/payment-gateway
 */
class CachedCashBillApiClient implements CashBillApiClientInterface
{
    private const CACHE_TTL = 86400;

    private const OMMIT_METHODS = [
        'getSignature',
        'stringToSign'
    ];

    private static CashBillApiClientInterface $client;

    private static CacheInterface $cache;

    public function __construct(
        CashBillApiClientInterface $client,
        CacheInterface $cache,
    ) {
        self::$client = $client;
        self::$cache = $cache;
    }

    public function getAvailableMethods(): array
    {


    }
}
