<?php
declare(strict_types=1);

use App\Application\Middleware\PermissionMiddleware;
use App\Repositories\Mysql\Router\PersistenceRepository;
use App\Application\Middleware\OAuthServerMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
  $app->options('/{routes:.*}', function (Request $request, Response $response) {
    // CORS Pre-Flight OPTIONS Request Handler
    return $response;
  });

  $app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('<h1 style="text-align: center;">Hello PHP!</h1>');
    return $response;
  });

  $PermissionMiddleware = new PermissionMiddleware($app->getContainer()->get(PersistenceRepository::class));
  $OAuthServerMiddleware = new OAuthServerMiddleware($app->getContainer());

  // 加载组件
  foreach (glob(realpath('../wanphp/components') . "/*/src/routes.php") as $filename) {
    $routes = require $filename;
    $routes($app, $PermissionMiddleware, $OAuthServerMiddleware);
  }
  // 加载插件
  foreach (glob(realpath('../wanphp/plugins') . "/*/src/routes.php") as $filename) {
    $routes = require $filename;
    $routes($app, $PermissionMiddleware, $OAuthServerMiddleware);
  }
  //公众号
  //$app->map(['GET', 'POST'], '/weixin', \App\Application\Api\Weixin\WePublic::class);

  $app->group('/api', function (Group $group) use ($PermissionMiddleware) {
    $group->get('/clearCache[/{db:[0-9]+}]', \App\Application\Api\Common\ClearCacheApi::class);
    //文件上传
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/files[/{id:[0-9]+}]', \App\Application\Api\Common\FilesApi::class);

    //系统管理
    $group->group('/manage', function (Group $g) {
      $g->get('/syncrouter', \App\Application\Api\Common\SyncRouterApi::class);
      $g->map(['GET', 'PATCH'], '/router[/{id:[0-9]+}]', \App\Application\Api\Common\RouterApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/navigate[/{id:[0-9]+}]', \App\Application\Api\Common\NavigateApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/clients[/{id:[0-9]+}]', \App\Application\Api\Manage\ClientsApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/admin[/{id:[0-9]+}]', \App\Application\Api\Manage\Admin\AdminApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/admin/role[/{id:[0-9]+}]', \App\Application\Api\Manage\Admin\RoleApi::class);
      $g->get('/admin/binduser/{uid:[0-9]+}', \App\Application\Api\Manage\Admin\BindUserApi::class);
    })->addMiddleware($PermissionMiddleware);
  })->addMiddleware($OAuthServerMiddleware);


  $app->group('/auth', function (Group $group) {
    $group->post('/authorize', \App\Application\Api\Auth\AuthorizeApi::class);
    $group->post('/accessToken', \App\Application\Api\Auth\AccessTokenApi::class);
    $group->post('/passwordAccessToken', \App\Application\Api\Auth\PasswordAccessTokenApi::class);
    $group->post('/miniProgramAccessToken', \App\Application\Api\Auth\MiniProgramAccessTokenApi::class);
    $group->post('/refreshAccessToken', \App\Application\Api\Auth\RefreshAccessTokenApi::class);
    $group->post('/initSys', \App\Application\Api\Common\InitSysApi::class);
  });
};
