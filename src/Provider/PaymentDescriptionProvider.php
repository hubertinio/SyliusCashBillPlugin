<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;

use Symfony\Contracts\Translation\TranslatorInterface;

final class PaymentDescriptionProvider implements PaymentDescriptionProviderInterface
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getPaymentDescription(PaymentInterface $payment): string
    {
        $order = $payment->getOrder();

        return $this->translator->trans('hubertinio_sylius_cashbill_plugin.ui.transaction.title', ['number' => $order->getNumber()]);
    }
}