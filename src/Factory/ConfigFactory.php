<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class ConfigFactory
{
    public function __construct(
        private RepositoryInterface $gatewayConfigRepository
    ) {
    }

    public function __invoke(): Config {
        $gatewayConfig = $this->gatewayConfigRepository->findOneBy(['factoryName' => CashBillBridgeInterface::NAME]);
        Assert::notNull($gatewayConfig, 'You need to configure CashBill payment method');

        $settings = $gatewayConfig->getConfig();
        Assert::keyExists($settings, 'app_id');
        Assert::keyExists($settings, 'app_secret');
        Assert::keyExists($settings, 'environment');

        return new Config(
            $settings['app_id'],
            $settings['app_secret'],
            $settings['environment']
        );
    }

    public function create(array $data): Config
    {
        return new Config(
            $data['app_id'],
            $data['app_secret'],
            $data['environment'],
        );
    }
}