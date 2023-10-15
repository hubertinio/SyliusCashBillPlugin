<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CashBillBridge implements CashBillBridgeInterface
{
    public function __construct(
        private RepositoryInterface $paymentRepository,
    ) {
    }

    public function capture(Payment $model, TransactionRequest $request, TransactionResponse $response): void
    {
        $details = $model->getDetails();

        $details['cashBillUrl'] = $response->redirectUrl;
        $details['cashBillId'] = $response->id;
        $details['cashBillSign'] = $request->sign;

        $model->setDetails($details);
        $this->paymentRepository->add($model);
    }

    public function retrieve(string $orderId): TransactionResponse
    {
        throw new \Exception("@TODO " . __METHOD__ . print_r(func_get_args(), true));
    }

    public function consumeNotification($data): ?TransactionResponse
    {

        $data;

    }
}