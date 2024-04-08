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
    \App\Domain\Admin\AdminGroupInterface::class => \DI\autowire(\App\Repositories\Mysql\Admin\AdminGroupRepository::class),
    \App\Domain\Common\RouterInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\RouterRepository::class),
    \App\Domain\Common\NavigateInterface::class => \DI\autowire(\App\Repositories\Mysql\Router\NavigateRepository::class),
    \App\Repositories\Mysql\Router\PersistenceRepository::class => \DI\autowire(\App\Repositories\Mysql\Router\PersistenceRepository::class),
    \App\Domain\Common\SettingInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\SettingRepository::class),
    \App\Domain\Common\FilesInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\FilesRepository::class),
    \App\Domain\Common\LinksInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\LinksRepository::class),
    \Wanphp\Libray\Slim\UploaderInterface::class => \DI\autowire(\App\Repositories\Mysql\Common\UploaderRepository::class),
    \App\Domain\Report\ReportInterface::class => \DI\autowire(\App\Repositories\Mysql\Report\ReportRepository::class),
    \App\Domain\Article\BasicInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicRepository::class),
    \App\Domain\Article\BasicAttachmentInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicAttachmentRepository::class),
    \App\Domain\Article\BasicAudioInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicAudioRepository::class),
    \App\Domain\Article\BasicAuditInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicAuditRepository::class),
    \App\Domain\Article\BasicCategoryInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicCategoryRepository::class),
    \App\Domain\Article\BasicContentInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicContentRepository::class),
    \App\Domain\Article\BasicCorrespondentInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicCorrespondentRepository::class),
    \App\Domain\Article\BasicImagesInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicImagesRepository::class),
    \App\Domain\Article\BasicLogInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicLogRepository::class),
    \App\Domain\Article\BasicReporterInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicReporterRepository::class),
    \App\Domain\Article\BasicSpecialModuleInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicSpecialModuleRepository::class),
    \App\Domain\Article\BasicSpecialImagesInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicSpecialImagesRepository::class),
    \App\Domain\Article\BasicSpecialTemplateInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicSpecialTemplateRepository::class),
    \App\Domain\Article\BasicStatisticsInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicStatisticsRepository::class),
    \App\Domain\Article\BasicTagInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicTagRepository::class),
    \App\Domain\Article\BasicTemplateInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicTemplateRepository::class),
    \App\Domain\Article\BasicVideoInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\BasicVideoRepository::class),
    \App\Domain\Article\CategoryInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\CategoryRepository::class),
    \App\Domain\Article\TagInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\TagRepository::class),
    \App\Domain\Article\DisableSearchInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\DisableSearchRepository::class),
    \App\Domain\Article\NewsSourceInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\NewsSourceRepository::class),
    \App\Domain\Article\NewsSpecialInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\NewsSpecialRepository::class),
    \App\Domain\Article\AuditAdminInterface::class => \DI\autowire(\App\Repositories\Mysql\Article\AuditAdminRepository::class),
    \App\Domain\Banner\BannerInterface::class => \DI\autowire(\App\Repositories\Mysql\Banner\BannerRepository::class),
    \App\Domain\Banner\BannerImageInterface::class => \DI\autowire(\App\Repositories\Mysql\Banner\BannerImageRepository::class),
    \App\Domain\Banner\NewsModuleInterface::class => \DI\autowire(\App\Repositories\Mysql\Banner\NewsModuleRepository::class),
    \App\Domain\Banner\ModuleNewsInterface::class => \DI\autowire(\App\Repositories\Mysql\Banner\ModuleNewsRepository::class)
  ]);
};
