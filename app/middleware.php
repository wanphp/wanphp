<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
  $paths = [ROOT_PATH . '/var/templates'];
  // 插件模板
  foreach (glob(ROOT_PATH . '/wanphp/plugins/*/templates') as $path) {
    $arr = explode('/', $path);
    $namespace = $arr[count($arr) - 2];
    $paths[$namespace] = $path;
  }
  $app->add(TwigMiddleware::create($app, Twig::create($paths)));//, ['cache' => __DIR__ . '/../var/cache']
  $app->add(new SessionMiddleware($app->getContainer()->get(\Wanphp\Libray\Slim\Setting::class)->get('oauth2Config')['encryptionKey'], $app->getContainer()->get(\Wanphp\Libray\Slim\Setting::class)->get('sessionName')));
  $app->addRoutingMiddleware();
  $app->add(new MethodOverrideMiddleware());
  $app->addMiddleware(new \App\Application\Middleware\JsonBodyParserMiddleware());
};
