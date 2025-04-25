<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;
use Wanphp\Libray\Slim\Setting;

return function (ContainerBuilder $containerBuilder) {
  // Global Settings Object
  $containerBuilder->addDefinitions([
    Setting::class => function () {
      return new Setting([
        'displayErrorDetails' => true, // 显示错误详细信息，在生产中应设置为false
        'logger' => [
          'name' => 'slim-app',
          'path' => ROOT_PATH . '/var/logs',
          'level' => Logger::DEBUG
        ],
        'uploadFilePath' => ROOT_PATH . '/var/uploadfiles',
        'oauth2Config' => [
          // openssl genrsa -aes128 -passout pass:wanphp@1122 -out private.key 2048
          // openssl rsa -in private.key -passin pass:wanphp@1122 -pubout -out public.key
          //'privateKey' => ROOT_PATH . '/var/conf/key/private.key',
          //'privateKeyPass' => 'wanphp@1122',
          // 资源服务器使用
          'publicKey' => ROOT_PATH . '/var/conf/key/public.key',
          // echo Key::createNewRandomKey()->saveToAsciiSafeString();
          'encryptionKey' => 'def000008bd8e66117fe24fd2dacc6c3b777598bbe740dae5581f74fe9363d09c36ae8beaa12b5ef16d091a46fb5ef6c914cf94c2fbac04a2615556a34e7c9f98ed2c397',
          'scope' => 'snsapi_base' // 获取微信用户，snsapi_base只取openid,snsapi_userinfo取完整信息
        ],
        // todo 自定义存储服务器
        'AuthCodeStorage' => new \App\Repositories\RedisCacheRepository(
          new Predis\Client(['scheme' => 'tcp', 'host' => 'redis', 'password' => 'wanphp#1122', 'port' => 6379, 'database' => 2], ['prefix' => 'uc:'])
        ),
        'userServer' => [
          'appId' => 'wanphp',
          'oauthServer' => 'https://users.wanphp.com/',
          'appSecret' => '',
          'apiUri' => 'https://users.wanphp.com/api/'
        ],
        'database' => [
          // required
          'database_type' => 'mysql',
          'database_name' => 'wanphp',
          'server' => 'mysql',
          'username' => 'root',
          'password' => 'wanphp@1122',

          // [optional]
          'charset' => 'utf8',
          'port' => 3306,
          'prefix' => 'wp_',
          'logging' => true,//启用日志及自动构建表结构，上传后关闭
          'option' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL
          ],
          'command' => [
            'SET SQL_MODE=ANSI_QUOTES'
          ],
          'error' => PDO::ERRMODE_SILENT
        ],
        'wechat.base' => [
          'appid' => '',
          'appsecret' => '',
          'token' => '',
          'encodingAesKey' => '',
          'uin_base64' => '', // 公众号的唯一ID
          'webAuthorization' => true // 公众号是否有网页授权获取用户基本信息权限
        ],
        'wechat.pay-v3' => [
          'appid' => '', // 确保该appid与merchantId有绑定关系
          'merchantId' => '',// 商户号
          'merchantCertificateSerial' => '',// 商户API证书序列号
          'merchantPrivateKeyFilePath' => '',// 商户API私钥
          'platformCertificateFilePath' => '',// 微信支付平台证书，用来验证微信支付应答的签名
          'platformCertificateSerial' => '',// 微信支付平台证书序列号,可以从「微信支付平台证书」文件解析，也可以在 商户平台 -> 账户中心 -> API安全 查询到
          'platformPublicKeyFilePath' => '',// 微信支付公钥，用来验证微信支付应答的签名
          'platformPublicKeyId' => '',// 微信支付公钥ID,需要在 商户平台 -> 账户中心 -> API安全 查询
          'apiV3Key' => '',
          'notify_url' => ''
        ]
      ]);
    },
    \App\Domain\Common\LogsInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\LogsRepository::class),
    // todo 自定义缓存库
    \Wanphp\Libray\Slim\CacheInterface::class => new \App\Repositories\RedisCacheRepository(
      new Predis\Client(['scheme' => 'tcp', 'host' => 'redis', 'password' => 'wanphp#1122', 'port' => 6379, 'database' => 1], ['prefix' => 'wp:'])
    )
  ]);
};
