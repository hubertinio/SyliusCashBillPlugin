<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class CashBillGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => CashBillBridgeInterface::NAME,
            'payum.factory_title' => 'CashBill',
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => CashBillBridgeInterface::ENVIRONMENT_SANDBOX,
                'app_id' => '',
                'app_secret' => '',
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['environment', 'app_id', 'app_secret'];
            $config['payum.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    $config['app_id'],
                    $config['app_secret'],
                    $config['environment'],
                ];
            };
        }

//        $config['payum.paths'] = array_replace([
//            'PayumStripe' => __DIR__.'/Resources/views',
//        ], $config['payum.paths'] ?: []);
    }
}