<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model;

final class Config implements ConfigInterface
{
    private static string $apiUrl = 'https://pay.cashbill.pl/ws/rest';
    private static string $appId;
    private static string $appSecret;

    public function __construct(string $appId, string $appSecret, ?string $apiUrl = null)
    {
        self::$appId = $appId;
        self::$appSecret = $appSecret;

        if (null !== $apiUrl) {
            self::$apiUrl = $apiUrl;
        }
    }

    public static function setAppId(string $appId): void
    {
        self::$appId = $appId;
    }

    public static function setAppSecret(string $appSecret): void
    {
        self::$appSecret = $appSecret;
    }

    public static function setApiUrl(string $url): void
    {
        self::$apiUrl = $url;
    }

    public static function getApiUrl(): string
    {
        return self::$apiUrl;
    }

    public static function getAppId(): string
    {
        return self::$appId;
    }

    public static function getAppSecret(): string
    {
        return self::$appSecret;
    }
}