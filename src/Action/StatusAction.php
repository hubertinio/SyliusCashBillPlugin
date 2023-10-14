<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface
{
    public function __construct(
        private CashBillBridgeInterface $bridge
    ){
    }

    public function setApi($api): void
    {
        if (false === is_array($api)) {
            throw new UnsupportedApiException('Not supported. Expected to be set as array.');
        }

        $this->bridge->setAuthorizationData(
            $api['environment'],
            $api['oauth_client_id'],
            $api['oauth_client_secret']
        );
    }

    public function execute($request): void
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = $model['statusCashBill'] ?? null;
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
        return ($request instanceof GetStatusInterface) && ($request->getModel() instanceof ArrayAccess);
    }
}