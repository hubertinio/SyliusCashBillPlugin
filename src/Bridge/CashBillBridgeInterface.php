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

    public const CANCELED_PAYMENT_STATUS = 'Abort';
    public const COMPLETED_PAYMENT_STATUS = 'PositiveFinish';
    public const REJECTED_STATUS = 'NegativeAuthorization';

    public function capture(Payment $model, TransactionRequest $request, TransactionResponse $response): void;

    public function checkNotification(Notify $notify): Payment;

    public function verifyDetails(Payment $payment, DetailsResponse $details): void;

    public function handleDetails(Payment $payment, DetailsResponse $details): void;

    public function handleStatusChange(string $cashBillId, string $sign): void;

    public function fetchDetails(string $cashBillId): DetailsResponse;
}
