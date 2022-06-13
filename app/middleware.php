<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

return function (App $app) {
  $app->add(TwigMiddleware::create($app, Twig::create(__DIR__ . '/../var/templates')));//, ['cache' => __DIR__ . '/../var/cache']
  $app->add(new SessionMiddleware($app->getContainer()->get('settings')['encryptionKey']));
  $app->addRoutingMiddleware();
  $app->add(new MethodOverrideMiddleware());
  $app->addMiddleware(new \App\Application\Middleware\JsonBodyParserMiddleware());
};
