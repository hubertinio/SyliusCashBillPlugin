<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Exception\CashBillException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
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
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private GenericTokenFactoryInterface $tokenFactory;

    public function __construct(
        private CashBillBridgeInterface $bridge,
        private PaymentDescriptionProviderInterface $paymentDescriptionProvider
    ) {
    }

    public function setApi($api): void
    {
        if (false === is_array($api)) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }

        $this->bridge->setAuthorizationData(
            $api['environment'],
            $api['app_id'],
            $api['app_secret']
        );
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        /** @var TokenInterface $token */
        $token = $request->getToken();
        $data = $this->prepareOrder($token, $payment->getOrder(), $payment);

        $result = $this->bridge->create($data);

        if (null !== $model['orderId']) {
            $response = $this->bridge->retrieve((string) $model['orderId'])->getResponse();
            Assert::keyExists($response->orders, 0);

            if (CashBillBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
                $model['statusPayU'] = $response->orders[0]->status;
                $request->setModel($model);
            }

            if (CashBillBridgeInterface::NEW_API_STATUS !== $response->orders[0]->status) {
                return;
            }
        }

        if (null !== $result) {
            $response = $result->getResponse();

            if ($response && CashBillBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
                $model['orderId'] = $response->orderId;
                $request->setModel($model);

                throw new HttpRedirect($response->redirectUri);
            }
        }

        throw CashBillException::createFromStatus($response->status);
    }

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayObject;
    }

    private function prepareOrder(TokenInterface $token, OrderInterface $order, PaymentInterface $payment): array
    {
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $data = [];

        $data['continueUrl'] = $token->getTargetUrl();
        $data['notifyUrl'] = $notifyToken->getTargetUrl();
        $data['customerIp'] = $order->getCustomerIp();
        $data['merchantPosId'] = OpenPayU_Configuration::getMerchantPosId();
        $data['description'] = $this->paymentDescriptionProvider->getPaymentDescription($payment);
        $data['currencyCode'] = $order->getCurrencyCode();
        $data['totalAmount'] = $order->getTotal();
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

        $buyer = [
            'email' => (string) $customer->getEmail(),
            'firstName' => (string) $customer->getFirstName(),
            'lastName' => (string) $customer->getLastName(),
            'language' => $this->getFallbackLocaleCode($order->getLocaleCode()),
        ];
        $data['buyer'] = $buyer;
        $data['products'] = $this->getOrderItems($order);

        return $data;
    }

    private function getOrderItems(OrderInterface $order): array
    {
        $itemsData = [];
        $items = $order->getItems();

        /** @var OrderItemInterface $item */
        foreach ($items as $key => $item) {
            $itemsData[$key] = [
                'name' => $item->getProductName(),
                'unitPrice' => $item->getUnitPrice(),
                'quantity' => $item->getQuantity(),
            ];
        }

        return $itemsData;
    }

    private function getFallbackLocaleCode(string $localeCode): string
    {
        return explode('_', $localeCode)[0];
    }
}