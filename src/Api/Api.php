<?php

/**
 * 企业微信相关接口
 */

namespace Wework\Api;

use Wework\Utils\HttpUtils;
use Wework\Utils\Utils;
use Exception;
use Wework\Utils\ErrorHelper\ApiError;
use Wework\Utils\ErrorHelper\Error;

abstract class Api
{
    // 响应数据 json
    public $rspJson = null;
    // 响应数据 str
    public $rspRawStr = null;

    // 获取应用 access token
    const GET_TOKEN = '/cgi-bin/gettoken?corpid=CORP_ID&corpsecret=SECRET';

    // ---------------------------------- 客户联系 -------------------------------------------

    // 获取配置了客户联系功能的成员列表
    const GET_FOLLOW_USER_LIST = '/cgi-bin/externalcontact/get_follow_user_list?access_token=ACCESS_TOKEN';
    // 获取客户列表
    const GET_EXTERNAL_CONTACT_LIST = '/cgi-bin/externalcontact/list?access_token=ACCESS_TOKEN';
    // 获取客户详情
    const GET_EXTERNAL_CONTACT_INFO = '/cgi-bin/externalcontact/get?access_token=ACCESS_TOKEN';
    // 批量获取客户详情
    const BATCH_GET_EXTERNAL_CONTACTS = '/cgi-bin/externalcontact/batch/get_by_user?access_token=ACCESS_TOKEN';
    // 获取企业标签库
    const GET_CORP_TAG_LIST = '/cgi-bin/externalcontact/get_corp_tag_list?access_token=ACCESS_TOKEN';
    // 添加企业客户标签
    const ADD_CORP_TAG = '/cgi-bin/externalcontact/add_corp_tag?access_token=ACCESS_TOKEN';
    // 编辑企业客户标签
    const EDIT_CORP_TAG = '/cgi-bin/externalcontact/edit_corp_tag?access_token=ACCESS_TOKEN';
    // 删除企业客户标签
    const DEL_CORP_TAG = '/cgi-bin/externalcontact/del_corp_tag?access_token=ACCESS_TOKEN';
    // 编辑客户企业标签
    const MARK_CORP_TAG = '/cgi-bin/externalcontact/mark_tag?access_token=ACCESS_TOKEN';
    // 创建企业群发
    const ADD_MSG_TEMPLATE = '/cgi-bin/externalcontact/add_msg_template?access_token=ACCESS_TOKEN';

    // 获取应用 access token
    protected function getAccessToken()
    {
    }

    // 刷新应用 access token
    protected function refreshAccessToken()
    {
    }

    /**
     * 发起请求
     *
     * @param string $url 请求的 URL 地址
     * @param string $method 请求方式
     * @param array $args 请求参数
     * @throws Exception 异常
     */
    protected function _httpCall(string $url, string $method, array $args)
    {
        if (!in_array($method, ['POST', 'GET'])) {
            throw new Exception([ApiError::ERR_MSG[ApiError::ILLEGAL_METHOD]]);
        }

        switch ($method) {
            case 'POST':
                $url = HttpUtils::makeUrl($url);
                $this->_httpPostParseToJson($url, $args);
                $this->_checkErrCode();
                break;
            case 'GET':
                if (count($args) > 0) {
                    foreach ($args as $key => $value) {
                        if ($value == null) {
                            continue;
                        }
                        if (strpos($url, '?')) {
                            $url .= ('&' . $key . '=' . $value);
                        } else {
                            $url .= ('?' . $key . '=' . $value);
                        }
                    }
                }
                $url = HttpUtils::makeUrl($url);
                $this->_httpGetParseToJson($url);
                $this->_checkErrCode();
                break;
        }
    }

    /**
     * 将 GET 请求响应的数据转成 JSON 格式
     * @param string $url
     * @param bool $refreshTokenWhenExpired
     * @return bool|string
     * @throws Exception
     */
    protected function _httpGetParseToJson(string $url, bool $refreshTokenWhenExpired = true)
    {
        $retryCnt        = 0;
        $this->rspJson   = null;
        $this->rspRawStr = null;

        while ($retryCnt < 2) {
            $realUrl = $url;

            if (strpos($url, "ACCESS_TOKEN")) {
                $token     = $this->getAccessToken();
                $realUrl   = str_replace("ACCESS_TOKEN", $token, $url);
                $tokenType = "ACCESS_TOKEN";
            } else {
                $tokenType = "NO_TOKEN";
            }

            $this->rspRawStr = HttpUtils::httpGet($realUrl);

            if (!Utils::notEmptyStr($this->rspRawStr)) {
                throw new Exception(ApiError::ERR_MSG[ApiError::RESPONSE_EMPTY]);
            }

            $this->rspJson = json_decode($this->rspRawStr, true);

            if (strpos($this->rspRawStr, "errcode") !== false) {
                $errCode = Utils::arrayGet($this->rspJson, "errcode");
                if ($errCode == 40014 || $errCode == 42001 || $errCode == 42007 || $errCode == 42009) {
                    if ("NO_TOKEN" != $tokenType && true == $refreshTokenWhenExpired) {
                        if ("ACCESS_TOKEN" == $tokenType) {
                            $this->refreshAccessToken();
                        }
                        $retryCnt += 1;
                        continue;
                    }
                }
            }
            return $this->rspRawStr;
        }
        return '';
    }

    /**
     * 将 POST 请求响应的数据转成 JSON 格式
     * @param string $url
     * @param array $args
     * @param bool $refreshTokenWhenExpired
     * @return array|mixed
     * @throws Exception
     */
    protected function _httpPostParseToJson(string $url, array $args, bool $refreshTokenWhenExpired = true)
    {
        $postData        = $args;
        $this->rspJson   = null;
        $this->rspRawStr = null;
        $retryCnt        = 0;

        while ($retryCnt < 2) {
            $realUrl = $url;

            if (strpos($url, "ACCESS_TOKEN")) {
                $token     = $this->getAccessToken();
                $realUrl   = str_replace("ACCESS_TOKEN", $token, $url);
                $tokenType = "ACCESS_TOKEN";
            } else {
                $tokenType = "NO_TOKEN";
            }
            $this->rspRawStr = HttpUtils::httpPost($realUrl, $postData);

            if (!Utils::notEmptyStr($this->rspRawStr)) {
                throw new Exception(ApiError::ERR_MSG[ApiError::RESPONSE_EMPTY]);
            }

            $json          = json_decode($this->rspRawStr, true);
            $this->rspJson = $json;

            $errCode = Utils::arrayGet($this->rspJson, "errcode");

            // token expired
            if ($errCode == 40014 || $errCode == 42001 || $errCode == 42007 || $errCode == 42009) {
                if ("NO_TOKEN" != $tokenType && true === $refreshTokenWhenExpired) {
                    if ("ACCESS_TOKEN" == $tokenType) {
                        $this->refreshAccessToken();
                    }
                    $retryCnt += 1;
                    continue;
                }
            }
            return $json;
        }

        return [];
    }

    /**
     * 校验返回 code
     * @throws Exception
     */
    protected function _checkErrCode()
    {
        $rsp = $this->rspJson;
        $raw = $this->rspRawStr;
        if (empty($rsp)) {
            return;
        }

        if (!is_array($rsp)) {
            throw new Exception(ApiError::ERR_MSG[ApiError::INVALID_PARAMS] . " " . gettype($rsp));
        }

        if (!array_key_exists("errcode", $rsp)) {
            return;
        }

        if (!is_int($rsp["errcode"])) {
            throw new Exception(ApiError::ERR_MSG[ApiError::INVALID_ERROR_CODE_TYPE] . " " . gettype($rsp["errcode"]) . ":" . $raw);
        }

        if ($rsp["errcode"] != Error::SUCCESS) {
            throw new Exception(ApiError::ERR_MSG[ApiError::RESPONSE_ERR] . " " . $raw);
        }
    }
}
