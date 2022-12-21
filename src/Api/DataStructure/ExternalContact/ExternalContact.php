<?php

namespace Wework\Api\DataStructure\ExternalContact;


use Wework\Utils\Utils;

class ExternalContact
{
    // 外部联系人的 userid
    public ?string $externalUserId = null;
    // 外部联系人在微信开放平台的唯一身份标识
    public ?string $unionId = null;
    // 用于分页查询的游标
    public ?string $cursor = null;
    // 企业成员的 userid
    public ?string $userId = null;
    // 企业成员的 userid 列表
    public ?array $userIds = null;
    // 返回的最大记录数
    public int $limit = 100;
    // 外部联系人的 userid 集合
    public ?array $externalUserList = null;
    // 添加了外部联系人的企业成员信息
    public array $followUserInfo = [];
    // 添加了外部联系人的企业 ID
    public array $followUserIds = [];
    // 用户所拥有的标签
    public array $tags = [];
    // 批次查询条数
    public static int $batchProcessUserLimit = 100;

    // 处理【获取配置了客户联系功能的成员列表】
    public static function handleGetFollowUserRsp(array $rsp): ExternalContact
    {
        $e = new ExternalContact();

        if (Utils::notEmptyArr($rsp['follow_user'] ?? [])) {
            $e->followUserIds = $rsp['follow_user'];
        }

        return $e;
    }

    // 校验【批量获取客户详情】请求参数
    public static function checkBatchGetArgs(ExternalContact $externalContact)
    {
        Utils::checkNotEmptyArray($externalContact->userIds, 'userid_list');
    }

    // 处理【批量获取客户详情】请求参数
    public static function handleBatchGetArgs(ExternalContact $externalContact): array
    {
        $args = [];

        Utils::setIfNotNull($externalContact->userIds, "userid_list", $args);
        Utils::setIfNotNull($externalContact->limit, "limit", $args);
        Utils::setIfNotNull($externalContact->cursor, "cursor", $args);

        return $args;
    }

    // 处理【批量获取客户详情】响应数据
    public static function handleBatchGetRsp(array $rsp): ExternalContact
    {
        $e = new ExternalContact();

        if (Utils::notEmptyStr($rsp['next_cursor'])) {
            $e->cursor = $rsp['next_cursor'];
        }

        if (Utils::notEmptyArr($rsp['external_contact_list'])) {
            $e->externalUserList = $rsp['external_contact_list'];
        }

        return $e;
    }

    // 校验【获取客户列表】请求参数
    public static function checkGetListArgs(ExternalContact $externalContact)
    {
        Utils::checkNotEmptyStr($externalContact->userId, 'userid');
    }

    // 处理【获取客户列表】请求参数
    public static function handleGetListArgs(ExternalContact $externalContact): array
    {
        $args = [];
        Utils::setIfNotNull($externalContact->userId, 'userid', $args);

        return $args;
    }

    // 处理【获取客户列表】响应数据
    public static function handleGetListRsp(array $rsp): ExternalContact
    {
        $e = new ExternalContact();

        if (Utils::notEmptyArr($rsp['external_userid'] ?? [])) {
            $e->externalUserList = $rsp['external_userid'];
        }

        return $e;
    }

    // 校验【获取客户详情】请求参数
    public static function checkGetArgs(ExternalContact $externalContact)
    {
        Utils::checkNotEmptyStr($externalContact->externalUserId, 'external_userid');
    }

    // 处理【获取客户详情】请求参数
    public static function handleGetArgs(ExternalContact $externalContact): array
    {
        $args = [];
        Utils::setIfNotNull($externalContact->externalUserId, "external_userid", $args);
        Utils::setIfNotNull($externalContact->cursor, "cursor", $args);

        return $args;
    }

    // 处理【获取客户详情】响应参数
    public static function handleGetRsp(array $rsp): ExternalContact
    {
        $e = new ExternalContact();
        if (Utils::notEmptyStr($rsp['next_cursor'] ?? '')) {
            $e->cursor = $rsp['next_cursor'];
        }

        if (array_key_exists('external_contact', $rsp)) {
            $e->externalUserId = $rsp['external_contact']['external_userid'] ?? null;
            $e->unionId        = $rsp['external_contact']['unionid'] ?? null;
        }

        if (Utils::notEmptyArr($rsp['follow_user'] ?? [])) {
            foreach ($rsp['follow_user'] ?? [] as $item) {
                $e->followUserIds = array_merge($e->followUserIds, [$item["userid"]]);

                $tags = ($item['tags'] && is_array($item['tags'])) ? array_column($item['tags'], 'tag_id') : [];

                if (isset($e->followUserInfo[$item['userid']])) {
                    $e->followUserInfo[$item['userid']] = array_merge($e->followUserInfo[$item['userid']]['tags'], $tags);
                } else {
                    $e->followUserInfo[$item['userid']]['tags'] = $tags;
                }

                if (!empty($item['tags']) && is_array($item['tags'])) {
                    $e->tags = array_merge($e->tags, array_column($item['tags'], 'tag_id'));
                }
            }
        }

        return $e;
    }
}
