<?php

namespace Hubertinio\SyliusCashBillPlugin\Model;

interface ConfigInterface
{
    public function isSandbox(): bool;

    public function setAppId(string $appId): void;

    public function setAppSecret(string $appSecret): void;

    public function setApiHost(string $url): void;

    public function getApiHost(): string;

    public function getAppId(): string;

    public function getAppSecret(): string;
}