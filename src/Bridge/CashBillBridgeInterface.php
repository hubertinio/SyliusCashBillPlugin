<?php

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\Payment;

interface CashBillBridgeInterface
{
    public const NAME = 'cashbill';

    public const ENVIRONMENT_SANDBOX = 'sandbox';
    public const ENVIRONMENT_PROD = 'prod';

    public const COMPLETED_PAYMENT_STATUS = 'PositiveFinish';

    public const NEW_API_STATUS = 'NEW';
    public const PENDING_API_STATUS = 'PENDING';
    public const COMPLETED_API_STATUS = 'COMPLETED';
    public const SUCCESS_API_STATUS = 'SUCCESS';
    public const CANCELED_API_STATUS = 'CANCELED';
    public const PENDING_PAYMENT_STATUS = 'PENDING';
    public const CANCELED_PAYMENT_STATUS = 'CANCELED';
    public const WAITING_FOR_CONFIRMATION_PAYMENT_STATUS = 'WAITING_FOR_CONFIRMATION';
    public const REJECTED_STATUS = 'REJECTED';

    public function capture(Payment $model, TransactionRequest $request, TransactionResponse $response): void;

    public function checkNotification(Notify $notify): Payment;

    public function verifyDetails(Payment $payment, DetailsResponse $details): void;

    public function handleDetails(Payment $payment, DetailsResponse $details): void;
}