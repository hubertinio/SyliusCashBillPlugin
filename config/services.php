<?php

declare(strict_types=1);

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Api\CachedCashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Calculator\PerCashBillOrderRateCalculator;
use Hubertinio\SyliusCashBillPlugin\Cli\DevCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\LoadPointsCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\LoadServicesCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\PingCommand;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Hubertinio\SyliusCashBillPlugin\Service\PushService;
use Hubertinio\SyliusCashBillPlugin\Service\SecurityService;
use Sylius\Bundle\CoreBundle\Form\Type\Shipping\Calculator\ChannelBasedPerUnitRateConfigurationType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $servicesIdPrefix  = 'hubertinio.cashbill.';
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set($servicesIdPrefix . 'config', Config::class)
        ->arg('$appId', 'rocketdesign.usermd.net')
        ->arg('$appSecret', '5f87f568373746f7bed1833a3a631a16')
        ->arg('$apiUrl', 'https://pay.cashbill.pl/testws/rest/');

    $services->set($servicesIdPrefix . 'api.client', CashBillApiClient::class)
        ->args([
            service($servicesIdPrefix . 'config'),
        ]);

    $services->set($servicesIdPrefix . 'api.cached_client', CachedCashBillApiClient::class)
        ->args([
        service($servicesIdPrefix . 'api.client'),
        service('cache.app'),
    ]);

    $services->set($servicesIdPrefix . 'cli.ping', PingCommand::class)
        ->tag('console.command')
        ->args([
        service($servicesIdPrefix . 'api.client'),
    ]);

    $services->set($servicesIdPrefix . 'cli.dev', DevCommand::class)
        ->tag('console.command')
        ->args([
        service($servicesIdPrefix . 'api.client'),
        service($servicesIdPrefix . 'api.cached_client'),
    ]);

    $services->alias(CashBillApiClientInterface::class, $servicesIdPrefix . 'api.cached_client');

//    $services->set($servicesIdPrefix . 'cli.load_points', LoadPointsCommand::class)
//        ->tag('console.command')
//        ->args([
//            service($servicesIdPrefix . 'api.cached_client'),
//        ]
//    );

    $services->set($servicesIdPrefix . 'cli.load_services', LoadServicesCommand::class)
        ->tag('console.command')
        ->args([
            service($servicesIdPrefix . 'api.cached_client'),
    ]);

    /**
     * @TODO controller od push ogarnąć
     */
//    $services->set($servicesIdPrefix . 'service.push', PushService::class);
//    $services->set($servicesIdPrefix . 'service.security', SecurityService::class);
};
