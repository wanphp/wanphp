<?php
declare(strict_types=1);

use App\Application\Middleware\PermissionMiddleware;
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

  //公众号
  $app->map(['GET', 'POST'], '/weixin', \App\Application\Api\Weixin\WePublic::class);


  $app->group('/api', function (Group $group) use ($app) {
    $group->get('/clearCache[/{db:[0-9]+}]', \App\Application\Api\Common\ClearCacheApi::class);
    //当前用户
    $group->map(['GET', 'PATCH'], '/user', \App\Application\Api\User\UserApi::class);

    //文件上传
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/files[/{id:[0-9]+}]', \App\Application\Api\Common\FilesApi::class);

    //系统管理
    $group->group('/manage', function (Group $g) {
      $g->get('/syncrouter', \App\Application\Api\Common\SyncRouterApi::class);
      $g->map(['GET', 'PATCH'], '/router', \App\Application\Api\Common\RouterApi::class);
      $g->map(['PUT', 'POST', 'DELETE'], '/navigate[/{id:[0-9]+}]', \App\Application\Api\Common\NavigateApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/clients[/{id:[0-9]+}]', \App\Application\Api\Manage\ClientsApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/admin[/{id:[0-9]+}]', \App\Application\Api\Manage\AdminApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/admin/role[/{id:[0-9]+}]', \App\Application\Api\Manage\RoleApi::class);
      $g->map(['GET', 'PATCH', 'POST'], '/users[/{id:[0-9]+}]', \App\Application\Api\Manage\UserApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/user/role[/{id:[0-9]+}]', \App\Application\Api\Manage\UserRoleApi::class);
      $g->map(['GET', 'PUT', 'POST', 'DELETE'], '/weixin/tag[/{id:[0-9]+}]', \App\Application\Api\Manage\Weixin\TagsApi::class);
      $g->map(['GET', 'POST', 'DELETE'], '/weixin/tplmsg[/{tplid}]', \App\Application\Api\Manage\Weixin\TemplateMessageApi::class);
    })->addMiddleware(new PermissionMiddleware(
      $app->getContainer()->get(\App\Repositories\Mysql\Router\PersistenceRepository::class),
      $app->getContainer()->get(\App\Repositories\Mysql\Admin\AdminRepository::class)
    ));
  })->addMiddleware(new \App\Application\Middleware\OAuthServerMiddleware(
    $app->getContainer(),
    $app->getContainer()->get(\App\Infrastructure\Database\Redis::class))
  );


  $app->group('/auth', function (Group $group) {
    $group->post('/authorize', \App\Application\Api\Auth\AuthorizeApi::class);
    $group->post('/accessToken', \App\Application\Api\Auth\AccessTokenApi::class);
    $group->post('/passwordAccessToken', \App\Application\Api\Auth\PasswordAccessTokenApi::class);
    $group->post('/miniProgramAccessToken', \App\Application\Api\Auth\MiniProgramAccessTokenApi::class);
    $group->post('/refreshAccessToken', \App\Application\Api\Auth\RefreshAccessTokenApi::class);
    $group->post('/initSys', \App\Application\Api\Common\InitSysApi::class);
  });
};
