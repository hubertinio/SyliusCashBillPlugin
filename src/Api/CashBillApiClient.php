<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Api;

use Hubertinio\SyliusCashBillPlugin\Model\ConfigInterface;
use Webmozart\Assert\Assert;

/**
 * @see https://www.apaczka.pl/integracje/
 */
class CashBillApiClient implements CashBillApiClientInterface
{
    private const SIGN_ALGORITHM = 'sha1';
    private const EXPIRES = '+30min';

    private static ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public static function request($route, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$config->getApiUrl() . $route);
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

    public static function buildRequest($route, $data = [] )
    {
        $data = json_encode($data);
        $expires = strtotime( self::EXPIRES );

        return [
//            'app_id' => self::$config->getAppId(),
            'request' => $data,
//            'expires' => $expires,
//            'signature' => self::getSignature(
//                self::stringToSign( self::$config->getAppId(), $route, $data, $expires ),
//                static::$appSecret
//            )
        ];
    }

    /**
     * Fetch order details
     */
//    public static function order($id)
//    {
//        return self::request( __FUNCTION__ . '/' . $id . '/' );
//    }
//
//    public static function orders($page = 1, $limit = 10)
//    {
//        return self::request( __FUNCTION__ . '/', [
//            'page' => $page,
//            'limit' => $limit
//        ]);
//    }


    public static function service_structure()
    {
        return self::request( __FUNCTION__ . '/');
    }


    public static function getSignature( string $string, string $key )
    {
        return hash_hmac( self::SIGN_ALGORITHM, $string, $key );
    }

    public static function stringToSign( string $appId, string $route, string $data,  int $expires )
    {
        return sprintf( "%s:%s:%s:%s", $appId, $route, $data, $expires );
    }
}
