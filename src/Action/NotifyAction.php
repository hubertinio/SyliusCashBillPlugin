<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Action;

use ArrayObject;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Identity;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private CashBillBridgeInterface $bridge,
        private RouterInterface $router,
        private LoggerInterface $logger
    ){
    }

    public function execute($request): void
    {
        /** @var Notify $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentSecurityToken $model */
        $model = $request->getModel();
        Assert::isInstanceOf($model, PaymentSecurityToken::class);

        try {
            $payment = $this->bridge->checkNotification($request);
            $detailsResponse = $this->bridge->fetchDetails($payment->getDetails()['cashBillId']);
            $this->bridge->verifyDetails($payment, $detailsResponse);
            $this->bridge->handleDetails($payment, $detailsResponse);

            $thankYouUrl = $this->router->generate('sylius_shop_order_thank_you', [], UrlGeneratorInterface::ABSOLUTE_URL);
            header("Location: {$thankYouUrl}");
            exit;
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());
            throw new HttpResponse('ERROR', 500);
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof PaymentSecurityToken;
    }
}
