<?php

namespace Wework\Examples;

require dirname(__DIR__) . "/../vendor/autoload.php";

use Wework\Api\CorpApi;
use Wework\Api\DataStructure\ExternalContact\Tag\CorpTag;
use Exception;

$config = require('./config.php');
$api    = new CorpApi($config['CORP_ID'], $config['SECRET']);

try {
    // 添加企业客户标签
    $c          = new CorpTag();
    $c->groupId = $config['JOIN_TRAINING_GROUP_ID'];
    $c->tags    = ['name' => '测试001号'];
    $r          = $api->addCorpTag($c);
    print_r($r->groups);
    print_r($r->tags);

    $tagId = $r->tags[0]['id'];
    // 编辑企业客户标签
    $c          = new CorpTag();
    $c->tagId   = $tagId;
    $c->tagName = '测试007';
    $api->editCorpTag($c);

    // 编辑客户企业标签
    $c                 = new CorpTag();
    $c->userId         = 'SuHong';
    $c->externalUserId = 'wm2AytCgAA2L8BK2ghke7xJMe9RnL3Ug';
    $c->addTags        = [$tagId];
    $api->markCorpTag($c);

    // 获取企业标签库
    $c          = new CorpTag();
    $c->groupId = $config['JOIN_TRAINING_GROUP_ID'];
    $r          = $api->getCorpTagList($c);
    print_r($r->groups);
    print_r($r->tags);

    // 删除客户企业标签
    $c            = new CorpTag();
    $c->tagIdList = [$tagId];
    $api->delCorpTag($c);
} catch (Exception $e) {
    echo $e->getMessage() . "\n";

    if (!empty($tagId)) {
        // 删除客户企业标签
        $c            = new CorpTag();
        $c->tagIdList = [];
        $api->delCorpTag($c);
    }
}


