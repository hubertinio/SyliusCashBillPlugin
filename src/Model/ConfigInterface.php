<?php

namespace Hubertinio\SyliusCashBillPlugin\Model;

interface ConfigInterface
{
    public function isSandbox(): bool;

    public function setAppId(string $appId): void;

    public function setAppSecret(string $appSecret): void;

    public function setApiUrl(string $url): void;

    public function getApiUrl(): string;

    public function getAppId(): string;

    public function getAppSecret(): string;
}