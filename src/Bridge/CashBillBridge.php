<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Amount;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\OrderPaymentTransitions;

final class CashBillBridge implements CashBillBridgeInterface
{
    public function __construct(
        private CashBillApiClientInterface $apiClient,
        private RepositoryInterface $paymentRepository,
        private FactoryInterface $stateMachineFactory,
        private OrderRepositoryInterface $orderRepository,
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
     * Check payment exists
     */
    public function checkNotification(Notify $notify): Payment
    {
        /** @var PaymentSecurityToken $token */
        $token = $notify->getModel();

        /** @var Identity $identity */
        $identity = $token->getDetails();
        Assert::nullOrIsInstanceOf($identity, Identity::class);

        $identityId = $identity->getId();

        /** @var Payment $payment */
        $payment = $this->paymentRepository->find($identityId);
        Assert::isInstanceOf($payment, Payment::class);
        $paymentDetails = $payment->getDetails();
        Assert::keyExists($paymentDetails, 'cashBillId');
        Assert::keyExists($paymentDetails, 'cashBillSign');
        Assert::keyExists($paymentDetails, 'cashBillUrl');

        return $payment;
    }

    public function fetchDetails(string $cashBillId): DetailsResponse
    {
        $detailsRequest = new DetailsRequest($cashBillId);
        return $this->apiClient->transactionDetails($detailsRequest);
    }

    public function verifyDetails(Payment $payment, DetailsResponse $details): void
    {
        Assert::eq($payment->getDetails()['cashBillId'], $details->id);
    }

    public function handleDetails(Payment $payment, DetailsResponse $details): void
    {
        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if ($stateMachine->can(PaymentTransitions::TRANSITION_PROCESS)) {
            $stateMachine->apply(PaymentTransitions::TRANSITION_PROCESS);
            $this->paymentRepository->add($payment);
        }
    }

    public function handleStatusChange(string $cashBillId, string $sign): void
    {
        $payment = null;
        $criteria = ['state' => 'new'];
        $payments = $this->paymentRepository->findBy($criteria);

        /** @var Payment $pay */
        foreach ($payments as $pay) {
            $paymentDetails = $pay->getDetails();

            if ($paymentDetails
                && isset($paymentDetails['cashBillId'])
                && isset($paymentDetails['cashBillSign'])
                && $paymentDetails['cashBillId'] === $cashBillId
                && $paymentDetails['cashBillSign'] === $sign
            ) {
                $payment = $pay;
                break;
            }
        }

        if ($payment instanceof Payment) {
            $detailsRequest = new DetailsRequest($cashBillId);
            $detailsResponse = $this->apiClient->transactionDetails($detailsRequest);

            $this->verifyDetails($payment, $detailsResponse);
            $responseTotal = $detailsResponse->amount->getValueAsCent();

            if (
                $responseTotal >= $payment->getOrder()->getTotal()
                && $detailsResponse->amount->currencyCode = $payment->getOrder()->getCurrencyCode()
            ) {
                $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

                if ($stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
                    $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
                    $this->paymentRepository->add($payment);
                }
            }


        }
    }
}
