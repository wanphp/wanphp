<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Mysql\Database;
use Wanphp\Libray\Slim\Setting;


return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    LoggerInterface::class => function (ContainerInterface $c) {
      $settings = $c->get(Setting::class);

      $loggerSettings = $settings->get('logger');
      $logger = new Logger($loggerSettings['name']);
      $logger->pushProcessor(new UidProcessor());

      $handler = new StreamHandler($loggerSettings['path'] . DIRECTORY_SEPARATOR . date('Ymd') . '.log', $loggerSettings['level']);
      $handler->setFormatter(new LineFormatter("[%datetime%][%channel%] %level_name%: %message% %context% %extra%\n", "Y-m-d H:i:s"));
      $logger->pushHandler($handler);

      return $logger;
    },
    Database::class => function (ContainerInterface $c) {
      $config = $c->get(Setting::class)->get('database');
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
    }
  ]);
  // 资源服务器，获取授权服务器用户信息
  if (class_exists('\Wanphp\Libray\User\User')) {
    $containerBuilder->addDefinitions([
      \Wanphp\Libray\Slim\WpUserInterface::class => \DI\autowire(\Wanphp\Libray\User\User::class)
    ]);
  }
  // 授权服务器
  if (class_exists('\Wanphp\Plugins\Weixin\Repositories\UserRepository')) {
    $containerBuilder->addDefinitions([
      \Wanphp\Libray\Slim\WpUserInterface::class => \DI\autowire(\Wanphp\Plugins\Weixin\Repositories\UserRepository::class)
    ]);
  }
};
