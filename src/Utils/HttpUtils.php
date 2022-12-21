<?php

namespace Wework\Utils;

use Exception;
use Wework\Utils\ErrorHelper\InternalError;
use Wework\Utils\ErrorHelper\NetworkError;

class HttpUtils
{
    const BASE = 'https://qyapi.weixin.qq.com';

    public static function makeUrl($queryArgs): string
    {
        if (substr($queryArgs, 0, 1) === DIRECTORY_SEPARATOR) {
            return self::BASE . $queryArgs;
        }
        return self::BASE . DIRECTORY_SEPARATOR . $queryArgs;
    }

    public static function array2Json($arr): string
    {
        $parts     = array();
        $isList    = false;
        $keys      = array_keys($arr);
        $maxLength = count($arr) - 1;

        if ($maxLength > 0 && ($keys[0] === 0) && ($keys[$maxLength] === $maxLength)) {
            $isList = true;
            for ($i = 0; $i < count($keys); $i++) {
                if ($i != $keys [$i]) {
                    $isList = false;
                    break;
                }
            }
        }

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                if ($isList) {
                    $parts [] = self::array2Json($value);
                } else {
                    $parts [] = '"' . $key . '":' . self::array2Json($value);
                }
            } else {
                $str = '';
                if (!$isList) {
                    $str = '"' . $key . '":';
                }
                if (!is_string($value) && is_numeric($value) && $value < 2000000000) {
                    $str .= $value;
                } elseif ($value === false) {
                    $str .= 'false';
                } elseif ($value === true) {
                    $str .= 'true';
                } else {
                    $str .= '"' . addcslashes($value, "\\\"\n\r\t/") . '"';
                }
                $parts[] = $str;
            }
        }
        $json = implode(',', $parts);
        if ($isList) {
            return '[' . $json . ']';
        }
        return '{' . $json . '}';
    }

    /**
     * http get
     *
     * @param $url
     * @return bool|string
     * @throws Exception
     */
    public static function httpGet($url)
    {
        self::__checkDeps();
        $ch = curl_init();

        self::__setSSLOpts($ch, $url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return self::__exec($ch);
    }

    /**
     * http post
     *
     * @param $url
     * @param $postData
     * @return bool|string
     * @throws Exception
     */
    public static function httpPost($url, $postData)
    {
        self::__checkDeps();
        $ch = curl_init();
        self::__setSSLOpts($ch, $url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        return self::__exec($ch);
    }

    private static function __setSSLOpts($ch, $url)
    {
        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }
    }

    /**
     * @param $ch
     * @return bool|string
     * @throws Exception
     */
    private static function __exec($ch)
    {
        $output = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        if ($output === false) {
            throw new Exception(NetworkError::ERR_MSG[NetworkError::NETWORK_ERR]);
        }

        if (intval($status["http_code"]) != 200) {
            throw new Exception(NetworkError::ERR_MSG[NetworkError::HTTP_STATUS_ERR] . ': ' . intval($status["http_code"]));
        }

        return $output;
    }

    /**
     * 校验 curl_init 是否存在
     * @throws Exception
     */
    private static function __checkDeps()
    {
        if (!function_exists("curl_init")) {
            throw new Exception(InternalError::ERR_MSG[InternalError::MISSING_CURL_EXTEND_ERR]);
        }
    }
}