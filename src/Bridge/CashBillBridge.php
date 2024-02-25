<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Bridge;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use InvalidArgumentException;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Notify;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

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
        Assert::inArray($details->status, [
            CashBillBridgeInterface::CANCELED_PAYMENT_STATUS,
            CashBillBridgeInterface::COMPLETED_PAYMENT_STATUS,
            CashBillBridgeInterface::REJECTED_STATUS,
        ]);
    }

    public function handleDetails(Payment $payment, DetailsResponse $details): void
    {
        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if ($stateMachine->can(PaymentTransitions::TRANSITION_PROCESS)) {
            $stateMachine->apply(PaymentTransitions::TRANSITION_PROCESS);
            $this->paymentRepository->add($payment);
        }
    }

    public function handleStatusChange(string $cashBillId, string $requestSign): void
    {
        $expectedSign = $this->apiClient->getStatusChangeSign($cashBillId);

        /**
         * @TODO check sign
         */
//        if ($expectedSign !== $requestSign) {
//            throw new InvalidArgumentException("Signature error");
//        }

        $transition = null;
        $payment = null;
        $criteria = [
            'state' => [
                PaymentInterface::STATE_NEW,
                PaymentInterface::STATE_PROCESSING,
                PaymentInterface::STATE_FAILED,
            ],
        ];

        $payments = $this->paymentRepository->findBy($criteria);

        /** @var Payment $item */
        foreach ($payments as $item) {
            if (($item->getDetails()['cashBillId'] ?? null) === $cashBillId) {
                $payment = $item;
                break;
            }
        }

        if (!$payment instanceof Payment) {
            return;
        }

        $detailsRequest = new DetailsRequest($cashBillId);
        $detailsResponse = $this->apiClient->transactionDetails($detailsRequest);

        $this->verifyDetails($payment, $detailsResponse);
        $responseTotal = $detailsResponse->amount->getValueAsCent();

        if (
            CashBillBridgeInterface::COMPLETED_PAYMENT_STATUS === $detailsResponse->status
            && $responseTotal >= $payment->getOrder()->getTotal()
            && $detailsResponse->amount->currencyCode = $payment->getOrder()->getCurrencyCode()
        ) {
            $transition = PaymentTransitions::TRANSITION_COMPLETE;
        } elseif (CashBillBridgeInterface::CANCELED_PAYMENT_STATUS === $detailsResponse->status) {
            $transition = PaymentTransitions::TRANSITION_CANCEL;
        } elseif (CashBillBridgeInterface::REJECTED_STATUS === $detailsResponse->status) {
            $transition = PaymentTransitions::TRANSITION_FAIL;
        }

        if (!$transition) {
            return;
        }

        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if ($stateMachine->can($transition)) {
            $stateMachine->apply($transition);
            $this->paymentRepository->add($payment);
        }
    }
}
