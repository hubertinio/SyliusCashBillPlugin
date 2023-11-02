<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Psr\Log\LoggerInterface;

final class StatusAction implements ActionInterface
{
    public function __construct(
        private CashBillBridgeInterface $bridge,
        private LoggerInterface $logger
    ){
    }

    public function execute($request): void
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $body = file_get_contents('php://input');
            $this->logger->debug(__METHOD__, ['body' => $body]);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = $model['status'] ?? null;
        $orderId = $model['orderId'] ?? null;

        if (null === $status || null !== $orderId) {
            $request->markNew();

            return;
        }

        $statusMap = match($status) {
            CashBillBridgeInterface::NEW_API_STATUS => 'markNew',
            CashBillBridgeInterface::PENDING_API_STATUS => 'markPending',
            CashBillBridgeInterface::CANCELED_API_STATUS => 'markCanceled',
            CashBillBridgeInterface::WAITING_FOR_CONFIRMATION_PAYMENT_STATUS => 'markSuspended',
            CashBillBridgeInterface::COMPLETED_API_STATUS => 'markCaptured',
            default => 'markUnknown',
        };

        $request->{$statusMap}();
    }

    public function supports($request): bool
    {
        $model = $request->getModel();

        $this->logger->debug(__METHOD__, [
            'request_class' => get_class($request),
            'request_interfaces' => json_encode(class_implements($request)),
            'model_class' => get_class($model),
            'model_interfaces' => json_encode(class_implements($model)),
            'model' => json_encode($model),
        ]);

        return ($request instanceof GetStatusInterface) && ($model instanceof ArrayAccess);
    }
}