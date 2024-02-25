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
use Hubertinio\SyliusCashBillPlugin\CashBillGatewayFactory;
use Hubertinio\SyliusCashBillPlugin\Cli\DevCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\FetchPaymentChannelsCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\FetchTransactionDetailsCommand;
use Hubertinio\SyliusCashBillPlugin\Cli\PingCommand;
use Hubertinio\SyliusCashBillPlugin\Factory\ApiClientFactory;
use Hubertinio\SyliusCashBillPlugin\Form\Type\CashBillGatewayConfigurationType;
use Hubertinio\SyliusCashBillPlugin\Provider\PaymentDescriptionProvider;
use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use Hubertinio\SyliusCashBillPlugin\Controller\StatusChangeNotificationController;

return static function (ContainerConfigurator $containerConfigurator): void {
    $servicesIdPrefix  = 'hubertinio_sylius_cashbill_plugin.';

    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();

    $services->set($servicesIdPrefix  . 'status_change_notification.controller', StatusChangeNotificationController::class)
        ->args([
            true,
            param('kernel.logs_dir'),
            service('filesystem'),
            service($servicesIdPrefix . 'bridge'),
        ]);

    $services->set($servicesIdPrefix . 'api.client_factory', ApiClientFactory::class)
        ->args([
            service('sylius.repository.gateway_config'),
            service('sylius.http_client'),
            service('sylius.http_message_factory'),
            service('serializer'),
            service('logger'),
        ]);

    $services->set($servicesIdPrefix . 'api.client', CashBillApiClient::class)
        ->factory(service($servicesIdPrefix . 'api.client_factory'))
        ->tag('payum.api.cashbill_client', [
            'factory' => CashBillBridgeInterface::NAME,
            'alias' => 'payum.api.cashbill',
        ])
    ;

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

    $services->alias(CashBillApiClientInterface::class, $servicesIdPrefix . 'api.client');

    $services->set($servicesIdPrefix . 'cli.fetch_channels', FetchPaymentChannelsCommand::class)
        ->tag('console.command')
        ->args([
            service($servicesIdPrefix . 'api.client'),
    ]);

    $services->set($servicesIdPrefix . 'cli.fetch_transaction_details', FetchTransactionDetailsCommand::class)
        ->tag('console.command')
        ->args([
            service($servicesIdPrefix . 'api.client'),
    ]);

    $services->set($servicesIdPrefix . 'gateway_factory_builder', GatewayFactoryBuilder::class)
        ->tag('payum.gateway_factory_builder', ['factory' => CashBillBridgeInterface::NAME])
        ->args([
            CashBillGatewayFactory::class
        ]);

    $services->set($servicesIdPrefix . 'bridge', CashBillBridge::class)
        ->args([
            service($servicesIdPrefix . 'api.client'),
            service('sylius.repository.payment'),
            service('sm.factory'),
            service('sylius.repository.order'),
        ]);

    $services->set($servicesIdPrefix . 'provider.payment_description_provider', PaymentDescriptionProvider::class)
        ->args([
            service('translator')
        ]);

    $services->set($servicesIdPrefix . 'form.type.gateway_configuration', CashBillGatewayConfigurationType::class)
        ->tag('form.type')
        ->tag('sylius.gateway_configuration_type', [
            'type' => CashBillBridgeInterface::NAME,
            'label' => 'hubertinio_sylius_cashbill_plugin.ui.gateway_label'
        ]);

    $services->set($servicesIdPrefix . 'action.capture', CaptureAction::class)
        ->tag('payum.action', [
            'factory' => CashBillBridgeInterface::NAME,
            'alias' => 'payum.action.capture'
        ])
        ->args([
            service($servicesIdPrefix . 'api.client'),
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
            service('router'),
            service('logger'),
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
};
