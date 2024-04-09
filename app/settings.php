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
          'encryptionKey' => 'def000008bd8e66117fe24fd2dacc6c3b777598bbe740dae5581f74fe9363d09c36ae8beaa12b5ef16d091a46fb5ef6c914cf94c2fbac04a2615556a34e7c9f98ed2c397'
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
          'encodingAesKey' => ''
        ],
        'wechat.miniprogram' => [
          'appid' => '',
          'appsecret' => '',
          'token' => '',
          'encodingAesKey' => ''
        ],
        'wechat.pay-v2' => [
          'appid' => '',// 绑定支付的APPID
          'mchid' => '',// 商户号
          'appSecret' => '',// 商户支付密钥
          'notifyUrl' => '',// 支付回调url
          'sslKeyPath' => '',// 证书密钥路径
          'sslCertPath' => ''// 微信支付平台证书路径
        ],
        'wechat.pay-v3' => [
          'merchantId' => '',// 商户号
          'merchantSerialNumber' => '',// 商户API证书序列号
          'pathToPrivateKey' => '',// 商户私钥
          'pathToCertificate' => ''// 微信支付平台证书
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
