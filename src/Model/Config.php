<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;

final class Config implements ConfigInterface
{
    private string $apiHost;

    public function __construct(
        private string $appId,
        private string $appSecret,
        private string $environment,
    ) {
        $this->apiHost = match($this->environment) {
            CashBillBridgeInterface::ENVIRONMENT_SANDBOX => 'https://pay.cashbill.pl/testws/rest/',
            default => 'https://pay.cashbill.pl/ws/rest/',
        };
    }

    public function isSandbox(): bool
    {
        return str_contains($this->apiHost, 'test');
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function setApiHost(string $url): void
    {
        $this->apiHost = $url;
    }

    public function getApiHost(): string
    {
        return $this->apiHost;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }
}