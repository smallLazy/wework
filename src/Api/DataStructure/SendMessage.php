<?php

namespace Wework\Api\DataStructure;

use Wework\Utils\Utils;

use Exception;

class SendMessage
{
    // 接收消息的成员 ID, 多个用'|'分隔,最多支持 1000 个. 特殊情况：指定为"@all"，则向该企业应用的全部成员发送
    public $toUser = null;
    // 接收消息的部门 ID, 多个用'|'分隔，最多支持 100 个. 当 touser 为 "@all" 时忽略本参数
    public $toParty = null;
    // 接收消息的标签 ID, 多个用‘|’分隔，最多支持 100 个. 当touser为"@all"时忽略本参数
    public $toTag = null;
    // 消息类型
    public $msgType = null;
    // 企业应用 ID
    public $agentId = null;
    // 消息内容，最长不超过 2048 个字节
    public $content = null;
    // 是否是保密消息 0:可对外分享, 1:不能分享且内容显示水印
    public $safe = null;
    // 是否开启 id 转译，0:否, 1:是
    public $enableIdTrans = null;
    // 是否开启重复消息检查 0:否, 1:是
    public $enableDuplicateCheck = null;
    // 是否重复消息检查的时间间隔, 默认1800s，最大不超过4小时
    public $duplicateCheckInterval = null;
    // 用户无权限/不存在
    public $invalidUser = null;
    // 部门无权限/不存在
    public $invalidParty = null;
    // 标签无权限/不存在
    public $invalidTag = null;
    // 未授权用户
    public $unlicensedUser = null;
    // 消息 ID
    public $msgId = null;

    /**
     * 校验消息参数
     *
     * @param $msg
     * @throws Exception
     */
    public static function checkSendMsgArgs($msg)
    {
        Utils::checkNotEmptyStr($msg->msgType, 'msgtype');
        Utils::checkNotEmptyStr($msg->agentId, 'agentid');
        Utils::checkNotEmptyStr($msg->content, 'content');
    }


    /**
     * 组装消息参数
     *
     * @param $sendMessage
     * @return array
     */
    public static function sendMessage2Array($sendMessage): array
    {
        $args = [];

        Utils::setIfNotNull($sendMessage->toUser, "touser", $args);
        Utils::setIfNotNull($sendMessage->toParty, "touser", $args);
        Utils::setIfNotNull($sendMessage->toTag, "totag", $args);
        Utils::setIfNotNull($sendMessage->msgType, "msgtype", $args);
        Utils::setIfNotNull($sendMessage->agentId, "agentid", $args);
        Utils::setIfNotNull($sendMessage->safe, "save", $args);
        Utils::setIfNotNull($sendMessage->enableIdTrans, "enable_id_trans", $args);
        Utils::setIfNotNull($sendMessage->enableDuplicateCheck, "enable_duplicate_check", $args);
        Utils::setIfNotNull($sendMessage->duplicateCheckInterval, "duplicate_check_interval", $args);
        self::setContentByType($sendMessage, $sendMessage->msgType, $args);

        return $args;
    }

    /**
     * 处理发送消息响应数据
     *
     * @param $rsp
     * @return SendMessage
     */
    public static function responseArray2SendMessage($rsp): SendMessage
    {
        $msg = new SendMessage();
        if (!empty($rsp['invaliduser'])) {
            $msg->invalidUser = $rsp['invaliduser'];
        }

        if (!empty($rsp['invalidparty'])) {
            $msg->invalidParty = $rsp['invalidparty'];
        }

        if (!empty($rsp['invalidtag'])) {
            $msg->invalidTag = $rsp['invalidtag'];
        }

        if (!empty($rsp['unlicenseduser'])) {
            $msg->unlicensedUser = $rsp['unlicenseduser'];
        }

        if (!empty($rsp['msgid'])) {
            $msg->msgId = $rsp['msgid'];
        }
        return $msg;
    }

    /**
     * 根据不同消息类型，设置消息内容
     *
     * @param $sendMessage
     * @param $msgType
     * @param $args
     */
    private static function setContentByType($sendMessage, $msgType, &$args)
    {
        switch ($msgType) {
            case 'text':
                Utils::setIfNotNull(['content' => $sendMessage->content], "text", $args);
                break;
            case "image":
                break;
        }
    }
}
