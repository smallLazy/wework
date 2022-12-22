<?php

namespace Wework\Callback;

class ExternalContactChange
{
    // 添加企业客户事件
    public static function add($xmlArray): bool
    {
        if (empty($xmlArray['Event']) || empty($xmlArray['ChangeType'])) {
            return false;
        }

        if ($xmlArray['ChangeType'] != 'add_external_contact') {
            return false;
        }

        return $xmlArray['ExternalUserID'];
    }
}