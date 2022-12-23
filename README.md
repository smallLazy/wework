# WeCome
official lib of wework api https://work.weixin.qq.com/api/doc
# How to Use

```
composer require smallLazy/wework
```

如果您希望覆盖存储库和条件所在的路径,请发布配置文件
```
php artisan vendor:publish
```
打开 config/qy.php 并编辑即可！


edit config
```
return [
    // 企业 ID
    'corp_id'                => '',
    // 秘钥
    'corp_secret'            => '',
    // 应用 ID
    'agent_id'               => '',
    // 应用回调 AES Key
    'aes_key'                => '',
    // 应用回调 Token
    'token'                  => '',
    // 加营组 ID
    'join_training_group_id' => ''
];
```

实例: src/Examples
 
