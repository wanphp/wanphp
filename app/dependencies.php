<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Predis\ClientInterface;
use Wanphp\Libray\Mysql\Database;
use Wanphp\Libray\Weixin\MiniProgram;
use Wanphp\Libray\Weixin\Pay;
use Wanphp\Libray\Weixin\WeChatPay;
use Wanphp\Libray\Weixin\WeChatBase;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    LoggerInterface::class => function (ContainerInterface $c) {
      $settings = $c->get('settings');

      $loggerSettings = $settings['logger'];
      $logger = new Logger($loggerSettings['name']);

      $processor = new UidProcessor();
      $logger->pushProcessor($processor);

      $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
      $logger->pushHandler($handler);

      return $logger;
    },
    ClientInterface::class => function (ContainerInterface $c) {
      $config = $c->get('redis');
      return new Predis\Client($config['parameters'], $config['options']);
    },
    Database::class => function (ContainerInterface $c) {
      $config = $c->get('database');
      try {
        $db = new Database($config);
      } catch (\Exception $e) {
        if (strpos($e->getMessage(), '[1049]')) {//数据库不存在，创建数据库,
          $database = $config['database_name'];
          $config['database_name'] = 'mysql';
          $db = new Database($config);
          //创建数据库
          $db->query("CREATE DATABASE IF NOT EXISTS `$database` default charset {$config['charset']} COLLATE {$config['charset']}_general_ci;");
          $db->query("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER ON `{$database}`.* TO 'webuser_rw'@'%';");
          $db->query("USE `{$database}`;");
        } else {
          throw $e;
        }
      }
      return $db;
    },
    MiniProgram::class => \DI\autowire(MiniProgram::class)->constructor(\DI\get('wechat.miniprogram'), \DI\get('redis')),
    Pay::class => \DI\autowire(Pay::class)->constructor(\DI\get('wechat.pay-v2')),
    WeChatPay::class => \DI\autowire(WeChatPay::class)->constructor(\DI\get('wechat.pay-v3')),
    WeChatBase::class => \DI\autowire(WeChatBase::class)->constructor(\DI\get('wechat.base'), \DI\get('redis'))
  ]);
};
