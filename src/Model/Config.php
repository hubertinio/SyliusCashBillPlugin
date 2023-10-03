<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model;

final class Config implements ConfigInterface
{
    private string $apiUrl = 'https://pay.cashbill.pl/ws/rest';
    private string $appId;
    private string $appSecret;

    public function __construct(string $appId, string $appSecret, ?string $apiUrl = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        if (null !== $apiUrl) {
            $this->apiUrl = $apiUrl;
        }
    }

    public function isSandbox(): bool
    {
        return str_contains($this->apiUrl, 'test');
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function setApiUrl(string $url): void
    {
        $this->apiUrl = $url;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
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