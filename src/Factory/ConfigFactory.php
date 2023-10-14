<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Config;

final class ConfigFactory
{

    public function __construct(private array $hosts)
    {
    }

    public function __invoke(): Config
    {
        $config = new Config(
            'rocketdesign.usermd.net',
            '5f87f568373746f7bed1833a3a631a16',
            $this->hosts[CashBillBridgeInterface::ENVIRONMENT_SANDBOX] ?? null,
        );

        return $config;
    }
}