<?php
declare(strict_types=1);

use App\Domain\Weixin\UserInterface;
use App\Repositories\Mysql\Weixin\userRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    UserInterface::class => \DI\autowire(userRepository::class),
    \App\Domain\Admin\AdminInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\AdminRepository::class),
    \App\Domain\Admin\RoleInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\RoleRepository::class),
    \App\Domain\Common\RouterInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\RouterRepository::class),
    \App\Domain\Common\NavigateInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\NavigateRepository::class),
    \App\Repositories\Mysql\Router\PersistenceRepository::class => \DI\autowire(\App\Repositories\Mysql\Router\PersistenceRepository::class),
    \App\Domain\Weixin\UserRoleInterface::class => \DI\autowire(\App\Repositories\Mysql\Weixin\userRoleRepository::class),
    \App\Domain\Common\ClientsInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\ClientsRepository::class),

  ]);
};
