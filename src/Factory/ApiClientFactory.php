<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Factory;

use GuzzleHttp\ClientInterface;
use Http\Message\MessageFactory;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiClientFactory
{
    public function __construct(
        private ConfigFactory $configFactory,
        private ClientInterface $client,
        private MessageFactory $messageFactory,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): CashBillApiClient
    {
        $client = new CashBillApiClient(
            $this->client,
            $this->messageFactory,
            $this->serializer,
            $this->logger
        );

        $config = $this->configFactory->__invoke();
        $client->setConfig($config);

        return $client;
    }
}