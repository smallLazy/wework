<?php

namespace Wework\Utils\ErrorHelper;

class NetworkError
{
    const NETWORK_ERR     = 40001;
    const HTTP_STATUS_ERR = 40002;

    const ERR_MSG = [
        self::NETWORK_ERR     => 'network error',
        self::HTTP_STATUS_ERR => 'unexpected http code'
    ];
}