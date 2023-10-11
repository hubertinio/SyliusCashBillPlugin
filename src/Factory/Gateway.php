<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use BitBag\SyliusPayUPlugin\Bridge\OpenPayUBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class Gateway extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'cashbill.factory_name' => 'cashbill',
                'cashbill.factory_title' => 'CashBill',
            ]
        );

        if (false === (bool) $config['cashbill.api']) {
            $config['cashbill.default_options'] = [
                'environment' => OpenPayUBridgeInterface::SANDBOX_ENVIRONMENT,
                'app_id' => '',
                'app_secret' => '',
            ];

            $config->defaults($config['cashbill.default_options']);
            $config['cashbill.required_options'] = ['app_id', 'app_secret'];
            $config['cashbill.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['cashbill.required_options']);

                return [
                    'environment' => $config['environment'],
                    'app_id' => $config['app_id'],
                    'app_secret' => $config['oauth_client_secret'],
                ];
            };
        }
    }
}