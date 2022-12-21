<?php

namespace Wework\Api\DataStructure\ExternalContact\MessagePush;

use Exception;
use Wework\Utils\Utils;

class AddMsgTemplate
{
    // 群发任务的类型, single:发送给客户, group:发送给客户群
    public ?string $chatType = null;
    // 客户的外部联系人 id 列表
    public ?array $externalUserId = null;
    // 发送企业群发消息的成员 userid，当类型为发送给客户群时必填
    public ?string $sender = null;
    // 是否允许成员在待发送客户列表中重新进行选择
    public ?bool $allowSelect = null;
    // 消息文本
    public ?array $text = null;
    // 附件信息
    public ?array $attachments = null;
    // 企业群发消息的 Id
    public string $msgId = '';
    // 无效或无法发送的 external_userid 列表
    public array $failList = [];


    // 校验【创建企业群发】参数
    public static function checkArgs(AddMsgTemplate $msgTemplate)
    {
//        Utils::checkNotEmptyArray($msgTemplate->attachments, 'attachments');
    }

    // 处理【创建企业群发】参数
    public static function handleArgs(AddMsgTemplate $msgTemplate): array
    {
        $args = [];
        Utils::setIfNotNull($msgTemplate->chatType, "chat_type", $args);
        Utils::setIfNotNull($msgTemplate->externalUserId, "external_userid", $args);
        Utils::setIfNotNull($msgTemplate->sender, "sender", $args);
        Utils::setIfNotNull($msgTemplate->allowSelect, "allow_select", $args);
        Utils::setIfNotNull($msgTemplate->text, "text", $args);
        Utils::setIfNotNull($msgTemplate->attachments, "attachments", $args);

        return $args;
    }

    // 处理【创建企业群发】响应数据
    public static function handleRsp($rsp): AddMsgTemplate
    {
        $m = new AddMsgTemplate();

        if (Utils::notEmptyStr($rsp['msgid'] ?? '')) {
            $m->msgId = $rsp['msgid'];
        }

        if (Utils::notEmptyArr($rsp['fail_list'] ?? [])) {
            $m->failList = $rsp['fail_list'];
        }

        return $m;
    }
}
