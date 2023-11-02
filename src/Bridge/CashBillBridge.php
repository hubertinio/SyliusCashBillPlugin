<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class CashBillBridge implements CashBillBridgeInterface
{
    public function __construct(
        private RepositoryInterface $paymentRepository,
    ) {
    }

    public function capture(Payment $model, TransactionRequest $request, TransactionResponse $response): void
    {
        $details = $model->getDetails();

        $details['cashBillId'] = $response->id;
        $details['cashBillSign'] = $request->sign;
        $details['cashBillUrl'] = $response->redirectUrl;

        $model->setDetails($details);
        $this->paymentRepository->add($model);
    }

    /**
     * sprawdzić czy płątnosć istnieje w bazie i porównać hash
     */
    public function checkNotification(Notify $notify): Payment
    {
        /** @var PaymentSecurityToken $token */
        $token = $notify->getModel();

        /** @var Identity $identity */
        $identity = $token->getDetails();
        Assert::nullOrIsInstanceOf($identity, Identity::class);

        /** @var Payment $payment */
        $payment = $this->paymentRepository->findOneBy(['id' => $identity->getId()]);
        Assert::isInstanceOf($payment, Payment::class);

        $this->verifyToken($payment, $token);

        return $payment;
    }

    public function verifyDetails(Payment $payment, DetailsResponse $details): void
    {
        $this;
    }

    public function handleDetails(Payment $payment, DetailsResponse $details): void
    {
        $this;
    }

    public function verifyToken(Payment $payment, PaymentSecurityToken $token): void
    {
        $paymentDetails = $payment->getDetails();
        Assert::keyExists($paymentDetails, 'cashBillId');
        Assert::keyExists($paymentDetails, 'cashBillSign');
        Assert::keyExists($paymentDetails, 'cashBillUrl');
        Assert::keyExists($paymentDetails, 'cashBillTokenHash');
        Assert::eq($paymentDetails['cashBillTokenHash'], $token->getHash());
    }
}