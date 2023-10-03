<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Api;

use Hubertinio\SyliusCashBillPlugin\Model\ConfigInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Channel;

/**
 * @see https://www.apaczka.pl/integracje/
 */
class CashBillApiClient implements CashBillApiClientInterface
{
    private const SIGN_ALGORITHM = 'sha1';
    private const EXPIRES = '+30min';

    private ConfigInterface $config;

    private SerializerInterface $serializer;

    public function __construct(
        ConfigInterface $config,
        SerializerInterface $serializer,
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    public function request($route, $data = null): string
    {
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

    /**
     * Fetch order details
     */
//    public function order($id)
//    {
//        return self::request( __FUNCTION__ . '/' . $id . '/' );
//    }
//
//    public function orders($page = 1, $limit = 10)
//    {
//        return self::request( __FUNCTION__ . '/', [
//            'page' => $page,
//            'limit' => $limit
//        ]);
//    }


    public function paymentChannels(): iterable
    {
        $json = self::request('paymentchannels/' . $this->config->getAppId());
        $data = json_decode($json);
        $channels = [];

        foreach ($data as $item) {
            $channels[] = Channel::createFromStdClass($item);

            if ($this->config->isSandbox()) {
                $item->id = 2;
                $item->name = 'Test 2';
                $channels[] = Channel::createFromStdClass($item);
            }
        }

        return $channels;
    }


    public function getSignature( string $string, string $key )
    {
        return hash_hmac( self::SIGN_ALGORITHM, $string, $key );
    }

    public function stringToSign( string $appId, string $route, string $data,  int $expires )
    {
        return sprintf( "%s:%s:%s:%s", $appId, $route, $data, $expires );
    }
}
