<?php
namespace Wework\Api;

use Wework\Api\DataStructure\ExternalContact\MessagePush\AddMsgTemplate;
use Wework\Api\DataStructure\ExternalContact\Tag\CorpTag;
use Wework\Api\DataStructure\ExternalContact\ExternalContact;
use Wework\Utils\HttpUtils;
use Wework\Utils\Utils;
use Exception;
use Wework\Utils\ErrorHelper\ApiError;

class CorpApi extends Api
{
    private   $corpId;
    private   $secret;
    protected $accessToken = null;

    /**
     * 企业进行自定义开发调用, 无需关注 accessToken, 会自动获取并刷新
     *
     * @param string $corpId : 企业 ID
     * @param string $secret : 应用的凭证密钥
     * @throws Exception
     */
    public function __construct(string $corpId, string $secret)
    {
        Utils::checkNotEmptyStr($corpId, "corpid");
        Utils::checkNotEmptyStr($secret, "secret");
        $this->corpId = $corpId;
        $this->secret = $secret;
    }

    // ------------------------- access token ---------------------------------

    /**
     * 获取 accessToken, 不用主动调用
     * @return void|null
     * @throws Exception
     */
    protected function getAccessToken()
    {
        if (!Utils::notEmptyStr($this->accessToken)) {
            $this->refreshAccessToken();
        }
        return $this->accessToken;
    }

    /**
     * 刷新 accessToken
     * @throws Exception
     */
    protected function refreshAccessToken()
    {
        if (!Utils::notEmptyStr($this->corpId) || !Utils::notEmptyStr($this->secret)) {
            throw new Exception(ApiError::ERR_MSG[ApiError::ILLEGAL_CORP_ID_OR_SECRET]);
        }

        $url = HttpUtils::makeUrl(self::GET_TOKEN);
        $url = str_replace("CORP_ID", $this->corpId, $url);
        $url = str_replace("SECRET", $this->secret, $url);

        $this->_httpGetParseToJson($url);
        $this->_checkErrCode();

        $this->accessToken = $this->rspJson["access_token"];
    }

    // ------------------------- 【客户联系】客户管理 ---------------------------------

    // 获取配置了客户联系功能的成员列表
    public function getFollowUserList(): ExternalContact
    {
        self::_httpCall(self::GET_FOLLOW_USER_LIST, 'GET', []);
        return ExternalContact::handleGetFollowUserRsp($this->rspJson);
    }

    // 获取客户列表
    public function getExternalContactList(ExternalContact $externalContact): ExternalContact
    {
        ExternalContact::checkGetListArgs($externalContact);
        $args = ExternalContact::handleGetListArgs($externalContact);
        self::_httpCall(self::GET_EXTERNAL_CONTACT_LIST, 'GET', $args);
        return ExternalContact::handleGetListRsp($this->rspJson);
    }

    // 获取客户详情
    public function getExternalContact(ExternalContact $externalContact): array
    {
        ExternalContact::checkGetArgs($externalContact);

        $r = ['externalUserId' => '', 'unionId' => '', 'followUserInfo' => [], 'tags' => [], 'followUserIds' => []];
        do {
            $args = ExternalContact::handleGetArgs($externalContact);
            self::_httpCall(self::GET_EXTERNAL_CONTACT_INFO, 'GET', $args);

            $externalRow = ExternalContact::handleGetRsp($this->rspJson);
            if (empty($r['externalUserId']) && !empty($externalRow->externalUserId)) {
                $r['externalUserId'] = $externalRow->externalUserId;
            }
            if (empty($r['unionId']) && !empty($externalRow->unionId)) {
                $r['unionId'] = $externalRow->unionId;
            }

            if (!empty($externalRow->followUserInfo)) {
                foreach ($externalRow->followUserInfo as $userId => $item) {
                    if (empty($r['followUserInfo'][$userId])) {
                        $r['followUserInfo'][$userId]['own_tags'] = $item['tags'];
                    } else {
                        $r['followUserInfo'][$userId]['own_tags'] = array_merge($r['followUserInfo'][$userId]['own_tags'], $item);
                    }
                }

                $r['tags']          = array_merge($r['tags'], $externalRow->tags);
                $r['followUserIds'] = array_merge($r['followUserIds'], $externalRow->followUserIds);
            }
            $nextCursor     = $externalRow->cursor;
            $args['cursor'] = $nextCursor;
        } while (!empty($nextCursor));

        return $r;
    }

    // 批量获取客户信息
    public function batchGetExternalContacts(ExternalContact $externalContact): ?array
    {
        ExternalContact::checkBatchGetArgs($externalContact);

        $r          = [];
        $userChunks = array_chunk($externalContact->userIds, ExternalContact::$batchProcessUserLimit);
        $external   = new ExternalContact();
        foreach ($userChunks as $userChunk) {
            $external->userIds = $userChunk;
            $args              = ExternalContact::handleBatchGetArgs($external);
            do {
                self::_httpCall(self::BATCH_GET_EXTERNAL_CONTACTS, 'POST', $args);
                $tmp = ExternalContact::handleBatchGetRsp($this->rspJson);
                if (!is_null($tmp->externalUserList)) {
                    $r = array_merge($r, $tmp->externalUserList);
                }

                $nextCursor     = $tmp->cursor;
                $args['cursor'] = $nextCursor;
            } while (!empty($nextCursor));
        }
        return $r;
    }

    // ------------------------- 【客户联系】客户标签管理 ---------------------------------

    // 获取企业标签库
    public function getCorpTagList(CorpTag $corpTag): CorpTag
    {
        $filter = CorpTag::handleListArgs($corpTag);
        self::_httpCall(self::GET_CORP_TAG_LIST, 'POST', $filter);
        return CorpTag::handleListRsp($this->rspJson);
    }

    // 添加企业客户标签
    public function addCorpTag(CorpTag $tags): CorpTag
    {
        CorpTag::checkAddArgs($tags);
        $args = CorpTag::handleAddArgs($tags);
        self::_httpCall(self::ADD_CORP_TAG, 'POST', $args);
        return CorpTag::handleAddRsp($this->rspJson);
    }

    // 编辑企业客户标签
    public function editCorpTag(CorpTag $tags)
    {
        CorpTag::checkEditArgs($tags);
        $args = CorpTag::handleEditArgs($tags);
        self::_httpCall(self::EDIT_CORP_TAG, 'POST', $args);
    }

    // 删除企业客户标签
    public function delCorpTag(CorpTag $tags)
    {
        CorpTag::checkDelArgs($tags);
        $args = CorpTag::handleDelArgs($tags);
        self::_httpCall(self::DEL_CORP_TAG, 'POST', $args);
    }

    // 编辑客户企业标签
    public function markCorpTag(CorpTag $tags)
    {
        CorpTag::checkMarkArgs($tags);
        $args = CorpTag::handleMarkArgs($tags);
        self::_httpCall(self::MARK_CORP_TAG, 'POST', $args);
    }

    // ------------------------- 【客户联系】消息推送 ---------------------------------

    // 创建企业群发
    public function addMsgTemplate(AddMsgTemplate $msgTemplate): AddMsgTemplate
    {
        AddMsgTemplate::checkArgs($msgTemplate);
        $args = AddMsgTemplate::handleArgs($msgTemplate);
        self::_httpCall(self::ADD_MSG_TEMPLATE, 'POST', $args);
        return AddMsgTemplate::handleRsp($this->rspJson);
    }
}