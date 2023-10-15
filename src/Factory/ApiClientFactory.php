<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use GuzzleHttp\ClientInterface;
use Http\Message\MessageFactory;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

final class ApiClientFactory
{
    public function __construct(
        private RepositoryInterface $gatewayConfigRepository,
        private ClientInterface $client,
        private MessageFactory $messageFactory,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): CashBillApiClient
    {
        $gatewayConfig = $this->gatewayConfigRepository->findOneBy(['factoryName' => CashBillBridgeInterface::NAME]);
        Assert::notNull($gatewayConfig, 'You need to configure CashBill payment method');

        $data = $gatewayConfig->getConfig();
        Assert::keyExists($data, 'app_id');
        Assert::keyExists($data, 'app_secret');
        Assert::keyExists($data, 'environment');

        return new CashBillApiClient(
            $data['app_id'],
            $data['app_secret'],
            $data['environment'],
            $this->client,
            $this->messageFactory,
            $this->serializer,
            $this->logger
        );
    }
}