<?php

/**
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 */

namespace Wework\Utils;


use App\Utils\ErrorHelper\QyWechatErrorHelper;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Log;

class XMLParse
{
    /**
     * 提取出 XML 数据包中的加密消息
     *
     * @param $xmlParams
     * @return array
     */
    public function extract($xmlParams): array
    {
        try {
            $xml = new DOMDocument();
            $xml->loadXML($xmlParams);
            $encryptArr = $xml->getElementsByTagName('Encrypt');
            $encrypt    = $encryptArr->item(0)->nodeValue;
            return [QyWechatErrorHelper::SUCCESS, $encrypt];
        } catch (Exception $e) {
            Log::error('extract catch error, ' . $e->getMessage());
            return [QyWechatErrorHelper::PARSE_XML_ERR, null];
        }
    }
}
