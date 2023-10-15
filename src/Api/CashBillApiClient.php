<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Api;

use http\Client;
use Http\Message\MessageFactory;
use Hubertinio\SyliusCashBillPlugin\Model\ConfigInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Channel;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @see https://api.cashbill.pl/category/api/payment-gateway
 */
class CashBillApiClient implements CashBillApiClientInterface
{
    private const SIGN_ALGORITHM = 'sha1';

    private ConfigInterface $config;

    public function __construct(
        private ClientInterface $client,
        private MessageFactory $messageFactory,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function setConfig(ConfigInterface $config): void
    {
        $this->config = $config;
    }

    public function paymentChannels(): iterable
    {
        $request = $this->messageFactory->createRequest(
            Request::METHOD_GET,
            $this->config->getApiHost() . 'paymentchannels/' . $this->config->getAppId(),
            ['Content-Type' => 'application/json']
        );

        try {
            $response = $this->client->send($request, ['verify' => true]);
        } catch (GuzzleException $e) {
            $this->logger->critical($e->getMessage());

            throw $e;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $channels = [];

        foreach ($data as $item) {
            $channels[] = Channel::createFromArray($item);

            if ($this->config->isSandbox()) {
                $item['id'] = 2;
                $item['name'] = 'Name 2';
                $item['description'] = 'Description 2';
                $channels[] = Channel::createFromArray($item);
            }
        }

        return $channels;
    }

    public function createTransaction(TransactionRequest $request): TransactionResponse
    {
        $request->sign = $this->getTransactionSign($request);
        $content = $this->serializer->serialize($request, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            'json_encode_options' => \JSON_PRESERVE_ZERO_FRACTION
        ]);

        $request = $this->messageFactory->createRequest(
            Request::METHOD_POST,
            $this->config->getApiHost() . 'payment/' . $this->config->getAppId(),
            ['Content-Type' => 'application/json'],
            $content,
        );

        try {
            $response = $this->client->send($request, ['verify' => true]);
        } catch (GuzzleException $e) {
            $this->logger->critical($e->getMessage());

            throw $e;
        }

        return $this->serializer->deserialize(
            $response->getBody()->getContents(),
            TransactionResponse::class,
            'json'
        );
    }

    public function getTransactionSign(TransactionRequest $request): string
    {
        $content = $request->title;
        $content .= $request->amount->value;
        $content .= $request->amount->currencyCode;
        $content .= $request->returnUrl;
        $content .= $request->description;
        $content .= $request->negativeReturnUrl;
        $content .= $request->additionalData;
        $content .= $request->paymentChannel;
        $content .= $request->languageCode;
        $content .= $request->referer;
        $content .= $request->personalData->firstName;
        $content .= $request->personalData->surname;
        $content .= $request->personalData->email;
        $content .= $request->personalData->country;
        $content .= $request->personalData->city;
        $content .= $request->personalData->postcode;
        $content .= $request->personalData->street;
        $content .= $request->personalData->house;
        $content .= $request->personalData->flat;
        $content .= $request->personalData->ip;

//        $content .= $request->optionsKeyValueList;
        $content .= $this->config->getAppSecret();

        return hash(self::SIGN_ALGORITHM, $content);
    }
}
