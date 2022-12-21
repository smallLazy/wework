<?php

namespace Wework\Utils;

use App\Utils\ErrorHelper\QyWechatErrorHelper;

class MsgCrypt
{
    private $m_sToken;
    private $m_sReceiveId;
    private $m_sEncodingAesKey;
    private $m_sEncodingAesKeyLen = 43;

    /**
     * @param string $token : 开发者设置的token
     * @param string $encodingAesKey : 开发者设置的EncodingAESKey
     * @param string $receiveId : 不同应用场景传不同的id
     */
    public function __construct(string $token, string $encodingAesKey, string $receiveId)
    {
        $this->m_sToken          = $token;
        $this->m_sEncodingAesKey = $encodingAesKey;
        $this->m_sReceiveId      = $receiveId;
    }

    /**
     * 验证 URL
     *
     * @param string $sMsgSignature : 签名串, 对应 URL 参数的 msg_signature
     * @param string $sTimeStamp : 时间戳, 对应 URL 参数的 timestamp
     * @param string $sNonce : 随机串, 对应 URL 参数的 nonce
     * @param string $sEchoStr : 随机串, 对应 URL 参数的 echostr
     * @param string $sReplyEchoStr : 解密之后的 echostr, 当return 返回 0 时有效
     * @return int: 成功 0, 失败返回对应的错误码
     */
    public function verifyUrl(
        string $sMsgSignature,
        string $sTimeStamp,
        string $sNonce,
        string $sEchoStr,
        string &$sReplyEchoStr
    ): int {
        if (strlen($this->m_sEncodingAesKey) != $this->m_sEncodingAesKeyLen) {
            return QyWechatErrorHelper::ILLEGAL_AES_KEY_ERR;
        }

        $encryptClass = new Encrypt($this->m_sEncodingAesKey);
        $sha          = new Sha();
        $shaData      = $sha->getSha1($this->m_sToken, $sTimeStamp, $sNonce, $sEchoStr);

        if ($shaData[0] != QyWechatErrorHelper::SUCCESS) {
            return $shaData[0];
        }

        if ($shaData[1] != $sMsgSignature) {
            return QyWechatErrorHelper::VALIDATE_SIGNATURE_ERR;
        }

        $decryptData = $encryptClass->decrypt($sEchoStr, $this->m_sReceiveId);
        if ($decryptData[0] != QyWechatErrorHelper::SUCCESS) {
            return $decryptData[0];
        }

        $sReplyEchoStr = $decryptData[1];
        return QyWechatErrorHelper::SUCCESS;
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param string $sMsgSignature : 签名串, 对应 URL 参数的 msg_signature
     * @param string $sNonce : 随机串, 对应 URL 参数的 nonce
     * @param string $sPostData : 密文, 对应 POST 请求的数据
     * @param string $sMsg : 解密后的原文, 当 return 返回 0 时有效
     * @param string|null $sTimeStamp : 时间戳对应 URL 参数的 timestamp
     * @return int: 成功 0, 失败返回对应的错误码
     */
    public function decryptMsg(
        string $sMsgSignature,
        string $sNonce,
        string $sPostData,
        string &$sMsg,
        string $sTimeStamp = null
    ): int {
        if (strlen($this->m_sEncodingAesKey) != 43) {
            return QyWechatErrorHelper::ILLEGAL_AES_KEY_ERR;
        }

        $pc = new Encrypt($this->m_sEncodingAesKey);

        //提取密文
        $xmlParse = new XMLParse();
        $array    = $xmlParse->extract($sPostData);

        if ($array[0] != QyWechatErrorHelper::SUCCESS) {
            return $array[0];
        }

        if ($sTimeStamp == null) {
            $sTimeStamp = time();
        }

        $encrypt = $array[1];

        //验证安全签名
        $sha   = new Sha();
        $array = $sha->getSha1($this->m_sToken, $sTimeStamp, $sNonce, $encrypt);

        if ($array[0] != QyWechatErrorHelper::SUCCESS) {
            return $array[0];
        }

        $signature = $array[1];
        if ($signature != $sMsgSignature) {
            return QyWechatErrorHelper::VALIDATE_SIGNATURE_ERR;
        }

        $result = $pc->decrypt($encrypt, $this->m_sReceiveId);
        if ($result[0] != QyWechatErrorHelper::SUCCESS) {
            return $result[0];
        }
        $sMsg = $result[1];

        return QyWechatErrorHelper::SUCCESS;
    }
}