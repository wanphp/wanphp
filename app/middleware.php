<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\App;

return function (App $app) {
  $app->add(new SessionMiddleware($app->getContainer()->get('settings')['privateKey']));
  $app->addRoutingMiddleware();
  $app->add(new MethodOverrideMiddleware());
  $app->addMiddleware(new \App\Application\Middleware\JsonBodyParserMiddleware());
};
