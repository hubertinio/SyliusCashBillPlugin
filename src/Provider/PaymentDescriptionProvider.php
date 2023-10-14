<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Provider;

use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class PaymentDescriptionProvider implements PaymentDescriptionProviderInterface
{
    public function getPaymentDescription(PaymentInterface $payment): string
    {
        $order = $payment->getOrder();

        return $order->getNumber();
    }
}