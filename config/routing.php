<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('hubertinio_sylius_cashbill_push_tracking', '/payments/cashbill/notify')
        ->controller(Hubertinio\SyliusCashbillPlugin\Controller\NotifyController::class . '::index');

//    $routes->add('hubertinio_sylius_cashbill_dynamic_welcome', '/dynamic-welcome/{name}')
//        ->controller(Hubertinio\SyliusCashbillPlugin\Controller\PushTrackingController::class . '::dynamicallyGreetAction');
};