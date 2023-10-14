<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class Gateway extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'hubertinio.cashbill.factory_name' => CashBillBridgeInterface::NAME,
                'hubertinio.cashbill.factory_title' => 'CashBill',
            ]
        );

        if (false === (bool) $config['hubertinio.cashbill.api']) {
            $config['hubertinio.cashbill.default_options'] = [
                'environment' => CashBillBridgeInterface::ENVIRONMENT_SANDBOX,
                'app_id' => '',
                'app_secret' => '',
            ];

            $config->defaults($config['hubertinio.cashbill.default_options']);
            $config['hubertinio.cashbill.required_options'] = ['app_id', 'app_secret'];
            $config['hubertinio.cashbill.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['hubertinio.cashbill.required_options']);

                return [
                    'environment' => $config['environment'],
                    'app_id' => $config['app_id'],
                    'app_secret' => $config['oauth_client_secret'],
                ];
            };
        }
    }
}