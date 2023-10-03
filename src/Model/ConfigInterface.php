<?php

namespace Hubertinio\SyliusCashBillPlugin\Model;

interface ConfigInterface
{
    public static function setAppId(string $appId): void;

    public static function setAppSecret(string $appSecret): void;

    public static function setApiUrl(string $url): void;

    public static function getApiUrl(): string;

    public static function getAppId(): string;

    public static function getAppSecret(): string;
}