<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  // 加载组件
  foreach (glob(realpath('../wanphp/components') . "/*/src/repositories.php") as $filename) {
    $repositories = require $filename;
    $repositories($containerBuilder);
  }
  // 加载插件
  foreach (glob(realpath('../wanphp/plugins') . "/*/src/repositories.php") as $filename) {
    $repositories = require $filename;
    $repositories($containerBuilder);
  }
  $containerBuilder->addDefinitions([
    \App\Domain\Admin\AdminInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\AdminRepository::class),
    \App\Domain\Admin\RoleInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\RoleRepository::class),
    \App\Domain\Common\RouterInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\RouterRepository::class),
    \App\Domain\Common\NavigateInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\NavigateRepository::class),
    \App\Repositories\Mysql\Router\PersistenceRepository::class => \DI\autowire(\App\Repositories\Mysql\Router\PersistenceRepository::class),
    \App\Domain\Common\SettingInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\SettingRepository::class),
    \App\Domain\Common\FilesInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\FilesRepository::class)
  ]);
};
