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
use Hubertinio\SyliusCashBillPlugin\Factory\Gateway;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Hubertinio\SyliusCashBillPlugin\Service\PushService;
use Hubertinio\SyliusCashBillPlugin\Service\SecurityService;
use Sylius\Bundle\CoreBundle\Form\Type\Shipping\Calculator\ChannelBasedPerUnitRateConfigurationType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator): void {
    $servicesIdPrefix  = 'hubertinio.cashbill.';
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set($servicesIdPrefix . 'config', Config::class)
        ->arg('$appId', param('hubertinio_sylius_cash_bill.app_id'))
        ->arg('$appSecret', param('hubertinio_sylius_cash_bill.app_secret'))
        ->arg('$apiHost', param('hubertinio_sylius_cash_bill.api_host'));

    $services->set($servicesIdPrefix . 'api.client', CashBillApiClient::class)
        ->args([
            service($servicesIdPrefix . 'config'),
            service('sylius.http_client'),
            service('sylius.http_message_factory'),
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
            service($servicesIdPrefix . 'api.client'),
    ]);

    /**
     * @TODO controller od push ogarnąć
     */
//    $services->set($servicesIdPrefix . 'service.push', PushService::class);
//    $services->set($servicesIdPrefix . 'service.security', SecurityService::class);

    $services->set($servicesIdPrefix . 'factory.gateway', Gateway::class)
//        ->tag('payum.gateway_factory_builder', ['factory' => 'payu'])
//        ->args([
//            service($servicesIdPrefix . 'api.client'),
//        ])
    ;

    $services->set($servicesIdPrefix . 'payum.gateway_factory', \Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder::class)
        ->tag('payum.gateway_factory_builder', ['factory' => 'payu'])
        ->args([
            service($servicesIdPrefix . 'factory.gateway'),
        ]);


};
