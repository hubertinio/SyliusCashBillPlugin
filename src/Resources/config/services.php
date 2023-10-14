<?php

declare(strict_types=1);

use Hubertinio\SyliusCashBillPlugin\Action\CaptureAction;
use Hubertinio\SyliusCashBillPlugin\Action\ConvertPaymentAction;
use Hubertinio\SyliusCashBillPlugin\Action\NotifyAction;
use Hubertinio\SyliusCashBillPlugin\Action\StatusAction;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridge;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Cli\DevCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\LoadServicesCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\PingCommand;
use Hubertinio\SyliusCashBillPlugin\Factory\Gateway;
use Hubertinio\SyliusCashBillPlugin\Form\Type\CashBillGatewayConfigurationType;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Hubertinio\SyliusCashBillPlugin\Provider\PaymentDescriptionProvider;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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

    $services->set($servicesIdPrefix . 'cli.load_services', LoadServicesCommand::class)
        ->tag('console.command')
        ->args([
            service($servicesIdPrefix . 'api.client'),
    ]);

    $services->set($servicesIdPrefix . 'factory.gateway', Gateway::class)
//        ->tag('payum.gateway_factory_builder', ['factory' => 'payu'])
//        ->args([
//            service($servicesIdPrefix . 'api.client'),
//        ])
    ;

    $services->set($servicesIdPrefix . 'gateway_factory', GatewayFactoryBuilder::class)
        ->tag('payum.gateway_factory_builder', ['factory' => CashBillBridgeInterface::NAME])
        ->args([
            service($servicesIdPrefix . 'factory.gateway'),
        ]);

    $services->set($servicesIdPrefix . 'bridge', CashBillBridge::class);

    $services->set($servicesIdPrefix . 'provider.payment_description_provider', PaymentDescriptionProvider::class);

    $services->set($servicesIdPrefix . 'form.type.gateway_configuration', CashBillGatewayConfigurationType::class)
        ->tag('form.type')
        ->tag('sylius.gateway_configuration_type', [
            'type' => CashBillBridgeInterface::NAME,
            'label' => $servicesIdPrefix . 'factory.gateway_label']
        )
    ;

    $services->set($servicesIdPrefix . 'action.capture', CaptureAction::class)
        ->args([
            service($servicesIdPrefix . 'bridge'),
            service($servicesIdPrefix . 'provider.payment_description_provider'),
        ]);

    $services->set($servicesIdPrefix . 'action.convert_payment', ConvertPaymentAction::class)
        ->tag('payum.action', [
            'factory' => CashBillBridgeInterface::NAME,
            'alias' => 'payum.action.convert_payment'
        ])
        ->args([
            service($servicesIdPrefix . 'bridge'),
            service($servicesIdPrefix . 'provider.payment_description_provider'),
        ]);

    $services->set($servicesIdPrefix . 'action.notify', NotifyAction::class)
        ->tag('payum.action', [
            'factory' => CashBillBridgeInterface::NAME,
            'alias' => 'payum.action.notify'
        ])
        ->args([
            service($servicesIdPrefix . 'bridge'),
            service('logger'),
        ]);

    $services->set($servicesIdPrefix . 'action.status', StatusAction::class)
        ->tag('payum.action', [
            'factory' => CashBillBridgeInterface::NAME,
            'alias' => 'payum.action.status'
        ])
        ->args([
            service($servicesIdPrefix . 'bridge'),
        ]);
};
