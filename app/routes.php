<?php
declare(strict_types=1);

use App\Application\Middleware\OAuthServerMiddleware;
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

  $PermissionMiddleware = new PermissionMiddleware($app->getContainer());
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
  $app->map(['GET', 'POST'], '/weixin', \App\Application\Api\Weixin\WePublic::class);

  $app->map(['GET', 'POST'], '/login', \App\Application\Actions\Common\LoginAction::class);
  $app->map(['GET', 'POST'], '/qrlogin', \App\Application\Actions\Common\QrLoginAction::class);
  $app->get('/loginout', \App\Application\Actions\Common\LoginOutAction::class);
  $app->get('/clearcache/{db:[0-9]+}', \App\Application\Api\Common\ClearCacheApi::class);

  $app->group('/admin', function (Group $group) {
    $group->get('/index', \App\Application\Actions\Home\HomeAction::class);
    $group->get('/actions', \App\Application\Actions\Permission\ListRouterAction::class);
    $group->get('/syncactions', \App\Application\Api\Common\SyncRouterApi::class);
    $group->patch('/router/{id:[0-9]+}', \App\Application\Api\Common\RouterApi::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/navigate[/{id:[0-9]+}]', \App\Application\Api\Common\NavigateApi::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/setting[/{id:[0-9]+}]', \App\Application\Actions\Common\SettingAction::class);
    $group->get('/admins', \App\Application\Actions\Admin\AdminAction::class);
    $group->map(['GET', 'POST'], '/editpassword', \App\Application\Actions\Admin\AdminInfoAction::class);
    $group->map(['GET', 'POST'], '/userbind', \App\Application\Actions\Admin\UserBindAction::class);
    $group->map(['PUT', 'POST', 'DELETE'], '/admins[/{id:[0-9]+}]', \App\Application\Api\Manage\Admin\AdminApi::class);
    $group->get('/roles', \App\Application\Actions\Admin\RoleAction::class);
    $group->map(['PUT', 'POST', 'DELETE'], '/roles[/{id:[0-9]+}]', \App\Application\Api\Manage\Admin\RoleApi::class);
    $group->get('/clients', \App\Application\Actions\Clients\ListAction::class);
    $group->map(['PUT', 'POST', 'DELETE'], '/clients[/{id:[0-9]+}]', \App\Application\Api\Manage\ClientsApi::class);

    //公众号
    $group->get('/weixin/tags', \App\Application\Actions\Weixin\UserTagsAction::class);
    $group->map(['PUT', 'POST', 'DELETE'], '/weixin/tags[/{id:[0-9]+}]', \Wanphp\Plugins\Weixin\Application\Manage\TagsApi::class);
    $group->get('/weixin/template', \App\Application\Actions\Weixin\TemplateMessageAction::class);
    $group->get('/weixin/users', \App\Application\Actions\Weixin\UserAction::class);
    $group->get('/weixin/users/search', \App\Application\Actions\Weixin\SearchUserAction::class);
    $group->patch('/weixin/users/{id:[0-9]+}', \Wanphp\Plugins\Weixin\Application\Manage\UserApi::class);

    //用户角色
    $group->get('/weixin/roles', \App\Application\Actions\Weixin\RoleAction::class);
    $group->map(['PUT', 'POST', 'DELETE'], '/weixin/roles[/{id:[0-9]+}]', \Wanphp\Plugins\Weixin\Application\Manage\UserRoleApi::class);

    //文件上传
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/files[/{id:[0-9]+}]', \App\Application\Api\Common\FilesApi::class);
  })->addMiddleware($PermissionMiddleware);

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
    $group->get('/authorize', \App\Application\Api\Auth\AuthorizeApi::class);
    $group->post('/accessToken', \App\Application\Api\Auth\AccessTokenApi::class);
    $group->post('/passwordAccessToken', \App\Application\Api\Auth\PasswordAccessTokenApi::class);
    $group->post('/miniProgramAccessToken', \App\Application\Api\Auth\MiniProgramAccessTokenApi::class);
    $group->post('/refreshAccessToken', \App\Application\Api\Auth\RefreshAccessTokenApi::class);
    $group->post('/initSys', \App\Application\Api\Common\InitSysApi::class);
  });
};