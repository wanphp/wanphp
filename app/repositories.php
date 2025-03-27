<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  // 加载组件
  foreach (glob(ROOT_PATH . '/wanphp/components/*/src/repositories.php') as $filename) {
    $repositories = require $filename;
    $repositories($containerBuilder);
  }
  // 加载插件
  foreach (glob(ROOT_PATH . '/wanphp/plugins/*/src/repositories.php') as $filename) {
    $repositories = require $filename;
    $repositories($containerBuilder);
  }
  $containerBuilder->addDefinitions([
    \App\Domain\Admin\AdminInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\AdminRepository::class),
    \App\Domain\Admin\RoleInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\RoleRepository::class),
    \App\Application\Common\Message\MessageInterface::class => \DI\autowire(\App\Application\Common\Message\OfficialAccount::class),
    \App\Domain\Admin\AdminGroupInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\AdminGroupRepository::class),
    \App\Domain\Common\RouterInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\RouterRepository::class),
    \App\Domain\Common\NavigateInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\NavigateRepository::class),
    \App\Repositories\Mysql\Router\PersistenceRepository::class => \DI\autowire(\App\Repositories\Mysql\Router\PersistenceRepository::class),
    \App\Domain\Common\SettingInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\SettingRepository::class),
    \App\Domain\Common\FilesInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\FilesRepository::class),
    \Wanphp\Libray\Slim\UploaderInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\UploaderRepository::class)
  ]);
};
