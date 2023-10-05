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

/**
 * @see https://www.apaczka.pl/integracje/
 *
 * @TODO Symfony Http Client Interface (CurlWrapper)
 */
class CashBillApiClient implements CashBillApiClientInterface
{
    private const SIGN_ALGORITHM = 'sha1';
    private const EXPIRES = '+30min';

    public function __construct(
        private ConfigInterface $config,
        private ClientInterface $client,
        private MessageFactory $messageFactory,
        private LoggerInterface $logger,
    ) {
    }

    public function request(RequestInterface $request): string
    {
//        $response = $this->httpClient->sendRequest($request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config->getApiUrl() . $route);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(self::buildRequest($route, $data)));

        $result = curl_exec($ch);

        if (false === $result)
        {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $result;
    }

    public function buildRequest($route, $data = [] )
    {
        $data = json_encode($data);
        $expires = strtotime( self::EXPIRES );

        return [
//            'app_id' => $this->config->getAppId(),
            'request' => $data,
//            'expires' => $expires,
//            'signature' => self::getSignature(
//                self::stringToSign( $this->config->getAppId(), $route, $data, $expires ),
//               ::$appSecret
//            )
        ];
    }

    public function paymentChannels(): iterable
    {
//        $content = [
//            'version' => SyliusCoreBundle::VERSION,
//            'hostname' => $request->getHost(),
//            'locale' => $request->getLocale(),
//            'user_agent' => $request->headers->get('User-Agent'),
//            'environment' => $this->environment,
//        ];


        $request = $this->messageFactory->createRequest(
            Request::METHOD_GET,
            $this->config->getApiUrl() . 'paymentchannels/' . $this->config->getAppId(),
            ['Content-Type' => 'application/json'],
//            json_encode($content),
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
                $item['name'] = 'Description 2';
                $channels[] = Channel::createFromArray($item);
            }
        }

        return $channels;
    }

    public function createTransation():
    {
//        $content = [
//            'version' => SyliusCoreBundle::VERSION,
//            'hostname' => $request->getHost(),
//            'locale' => $request->getLocale(),
//            'user_agent' => $request->headers->get('User-Agent'),
//            'environment' => $this->environment,
//        ];


        $request = $this->messageFactory->createRequest(
            Request::METHOD_GET,
            $this->config->getApiUrl() . 'paymentchannels/' . $this->config->getAppId(),
            ['Content-Type' => 'application/json'],
//            json_encode($content),
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
                $item['name'] = 'Description 2';
                $channels[] = Channel::createFromArray($item);
            }
        }

        return $channels;
    }
}
