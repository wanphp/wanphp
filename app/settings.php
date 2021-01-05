<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
  // Global Settings Object
  $containerBuilder->addDefinitions([
    'settings' => [
      'displayErrorDetails' => true, // 显示错误详细信息，在生产中应设置为false
      'logger' => [
        'name' => 'slim-app',
        'path' => isset($_ENV['docker']) ? 'php://stdout' : realpath('../') . '/var/logs/app.log',
        'level' => Logger::DEBUG,
      ],
      'privateKey' => realpath('../') . '/var/conf/key/private.key',
      'privateKeyPass' => 'wanphp@1122',
      'encryptionKey' => 'def000000488eaf61f323560adace212f12047a7ad5863f8905da1fb28ed290122f07256bf1d512bebf75c9177fdd06b369a7ce63684122b08e9a5884a6010f1ddaafcde',
      'uploadFilePath' => realpath('../') . '/var/uploadfiles',
      'authRedis' => 2
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
      ]
    ],
    'redis' => [
      'host' => 'redis',
      'password' => 'wanphp#1122',
      'prefix' => 'wp_',
      'port' => 6379,
      'database' => 1
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
};
