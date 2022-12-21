<?php

namespace Wework\Utils\ErrorHelper;

class InternalError
{
    const SYSTEM_ERR              = 10001;
    const MISSING_CURL_EXTEND_ERR = 10002;

    const ERR_MSG = [
        self::SYSTEM_ERR               => 'internal server error',
        self:: MISSING_CURL_EXTEND_ERR => 'missing curl extend',
    ];
}