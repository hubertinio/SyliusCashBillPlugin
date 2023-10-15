<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use ArrayObject;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Component\Core\Model\PaymentInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class NotifyAction implements ActionInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private CashBillBridgeInterface $bridge,
        private LoggerInterface $logger
    ){
    }

    public function execute($request): void
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        /** @var PaymentSecurityToken $model */
        $model = $request->getModel();

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $body = file_get_contents('php://input');
            $data = trim($body);

            try {
                $result = $this->bridge->consumeNotification($data);

                if (null !== $result) {
                    $response = $result->getResponse();

                    if ($response->order->orderId) {
                        $order = $this->bridge->retrieve($response->order->orderId);

                        if (CashBillBridgeInterface::SUCCESS_API_STATUS === $order->getStatus()) {
                            if (PaymentInterface::STATE_COMPLETED !== $payment->getState()) {
                                $status = $order->getResponse()->orders[0]->status;
                                $model['statusCashBill'] = $status;
                                $request->setModel($model);
                            }

                            throw new HttpResponse('SUCCESS');
                        }
                    }
                }
            } catch (Throwable $e) {
                $this->logger->critical($e->getMessage());
                throw new HttpResponse($e->getMessage(), 500);
            }
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof PaymentSecurityToken;
    }
}