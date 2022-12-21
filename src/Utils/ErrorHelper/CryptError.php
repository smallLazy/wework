<?php

namespace Wework\Utils\ErrorHelper;

class CryptError
{
    const VALIDATE_SIGNATURE_ERR = 20001;
    const COMPUTE_SIGNATURE_ERR  = 20002;
    const ILLEGAL_AES_KEY        = 20003;
    const VALIDATE_CORP_ID_ERR   = 20004;
    const ENCRYPT_AES_ERR        = 20005;
    const DECRYPT_AES_ERR        = 20006;
    const ILLEGAL_BUFFER         = 20007;
    const ENCODE_BASE64_ERR      = 20008;
    const DECODE_BASE64_ERR      = 20009;
    const PARSE_XML_ERR          = 20010;
    const GEN_XML_ERR            = 20011;
    const GEN_JSON_ERR           = 20012;
    const PARSE_JSON_ERR         = 20013;
    const ILLEGAL_PROTOCOL_TYPE  = 20014;

    const ERR_MSG = [
        self::VALIDATE_SIGNATURE_ERR => '签名校验失败',
        self::COMPUTE_SIGNATURE_ERR  => '计算签名异常',
        self::ILLEGAL_AES_KEY        => '非法的 AesKey',
        self::VALIDATE_CORP_ID_ERR   => 'CorpID 校验失败',
        self::ENCRYPT_AES_ERR        => 'AES 加密失败',
        self::DECRYPT_AES_ERR        => 'AES 解密失败',
        self::ILLEGAL_BUFFER         => '非法的 Buffer',
        self::ENCODE_BASE64_ERR      => 'Base64 加密失败',
        self::DECODE_BASE64_ERR      => 'Base64 解密失败',
        self::PARSE_XML_ERR          => 'XML 生成失败',
        self::GEN_XML_ERR            => 'XML 解析失败',
        self::GEN_JSON_ERR           => 'JSON 生成失败',
        self::PARSE_JSON_ERR         => 'JSON 解析失败',
        self::ILLEGAL_PROTOCOL_TYPE  => '非法的协议类型',
    ];
}
