<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use Faker\Factory;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Exception\CashBillException;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Amount;
use Hubertinio\SyliusCashBillPlugin\Model\Api\PersonalData;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Config;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethod;
use Throwable;
use Webmozart\Assert\Assert;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    private ?GenericTokenFactoryInterface $tokenFactory;

    public function __construct(
        private CashBillApiClient $apiClient,
        private CashBillBridgeInterface $bridge,
        private PaymentDescriptionProviderInterface $paymentDescriptionProvider,
    ) {
    }

    public function setApi($api): void
    {
        if (false === $api instanceof Config) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }

        $this->apiClient->setConfig($api);
    }

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof Payment;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        try {
            /** @var Payment $model */
            $model = $request->getModel();

            /** @var PaymentInterface $payment */
            $payment = $request->getFirstModel();

            $transaction = $this->prepareTransaction($request->getToken(), $payment->getOrder(), $payment);
            $result = $this->apiClient->createTransaction($transaction);

            throw new HttpRedirect($result->redirectUrl);
        } catch (Throwable $e) {
            throw CashBillException::createFromStatus($e->getMessage());
        }

    }

    private function prepareTransaction(TokenInterface $token, OrderInterface $order, PaymentInterface $payment): TransactionRequest
    {
        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        Assert::isInstanceOf(
            $customer,
            CustomerInterface::class,
            sprintf(
                'Make sure the first model is the %s instance.',
                CustomerInterface::class
            )
        );

        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $amount = Amount::createFromInt($order->getTotal(), $order->getCurrencyCode());

        $data = [];
        $data['continueUrl'] = $token->getTargetUrl();
        $data['notifyUrl'] = $notifyToken->getTargetUrl();
        $data['buyer'] = $customer->getUser();

        $personalData = new PersonalData();
        $personalData->email = (string) $customer->getEmail();
        $personalData->firstName = (string) $customer->getFirstName();
        $personalData->surname = (string) $customer->getLastName();
        $personalData->ip = $order->getCustomerIp();

        $title = $this->paymentDescriptionProvider->getPaymentDescription($payment);
        $language = $this->getFallbackLocaleCode($order->getLocaleCode());

        $transaction = new TransactionRequest(
            $title,
            $language,
            $amount,
            $personalData
        );

        $transaction->description = $this->getDescription($order);
        $transaction->returnUrl = $notifyToken->getTargetUrl();
        $transaction->negativeReturnUrl = $notifyToken->getTargetUrl();

        return $transaction;
    }

    private function getDescription(OrderInterface $order): string
    {
        $itemsData = [];
        $items = $order->getItems();

        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            $itemsData[] = sprintf(
                "%s (%d) x %d",
                $item->getProductName(),
                Amount::calcTotal($item->getUnitPrice()),
                $item->getQuantity()
            );
        }

        return implode(',', $itemsData);
    }

    private function getFallbackLocaleCode(string $localeCode): string
    {
        return explode('_', $localeCode)[0];
    }
}