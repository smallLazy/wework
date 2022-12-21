<?php

namespace Wework\Utils\ErrorHelper;


class ApiError
{
    const ILLEGAL_METHOD            = 30001;
    const RESPONSE_EMPTY            = 30002;
    const INVALID_ERROR_CODE_TYPE   = 30003;
    const RESPONSE_ERR              = 30004;
    const ILLEGAL_CORP_ID_OR_SECRET = 30005;
    const INVALID_PARAMS            = 30006;

    const ERR_MSG = [
        self::ILLEGAL_METHOD            => '非法的 method',
        self::RESPONSE_EMPTY            => 'response empty',
        self::INVALID_ERROR_CODE_TYPE   => 'invalid err code type',
        self::RESPONSE_ERR              => 'response error',
        self::ILLEGAL_CORP_ID_OR_SECRET => '非法的 corp id or secret',
        self::INVALID_PARAMS            => 'invalid params',
    ];
}
