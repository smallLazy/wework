<?php

/**
 * 计算公众平台的消息签名接口.
 */

namespace Wework\Utils;


use App\Utils\ErrorHelper\QyWechatErrorHelper;
use Exception;
use Illuminate\Support\Facades\Log;

class Sha
{
    /**
     * 用SHA1算法生成安全签名
     *
     * @param string $token : 票据
     * @param string $timestamp : 时间戳
     * @param string $nonce : 随机字符串
     * @param string $encryptMsg : 密文消息
     * @return array
     */
    public function getSha1(string $token, string $timestamp, string $nonce, string $encryptMsg): array
    {
        try {
            $params = [$encryptMsg, $token, $timestamp, $nonce];
            sort($params, SORT_STRING);
            return [QyWechatErrorHelper::SUCCESS, sha1(implode($params))];
        } catch (Exception $e) {
            Log::error('get sha1 error, ' . $e->getMessage());
            return [QyWechatErrorHelper::COMPUTE_SIGNATURE_ERR, null];
        }
    }
}