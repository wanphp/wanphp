<?php
declare(strict_types=1);

use App\Application\Middleware\PermissionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Wanphp\Plugins\Weixin\Application\OAuthServerMiddleware;

return function (App $app) {
  $app->options('/{routes:.*}', function (Request $request, Response $response) {
    // CORS Pre-Flight OPTIONS Request Handler
    return $response;
  });

  $PermissionMiddleware = new PermissionMiddleware($app->getContainer());
  $OAuthServerMiddleware = new OAuthServerMiddleware(
    $app->getContainer()->get(\Wanphp\Libray\Slim\Setting::class)->get('oauth2Config'),
    $app->getContainer()->get(\Wanphp\Libray\Slim\Setting::class)->get('AuthCodeStorage')
  );

  // 加载组件
  foreach (glob(ROOT_PATH . '/wanphp/components/*/src/routes.php') as $filename) {
    $routes = require $filename;
    $routes($app, $PermissionMiddleware, $OAuthServerMiddleware);
  }
  // 加载插件
  foreach (glob(ROOT_PATH . '/wanphp/plugins/*/src/routes.php') as $filename) {
    $routes = require $filename;
    $routes($app, $PermissionMiddleware, $OAuthServerMiddleware);
  }
  //公众号
  $app->map(['GET', 'POST'], '/weixin', \App\Application\Api\Weixin\WePublic::class);

  $app->map(['GET', 'POST'], '/login', \App\Application\Actions\Common\LoginAction::class);
  $app->map(['GET', 'POST'], '/qrLogin', \App\Application\Actions\Common\QrLoginAction::class);
  $app->get('/loginOut', \App\Application\Actions\Common\LoginOutAction::class);
  $app->get('/clearCache', \App\Application\Actions\Common\ClearCacheAction::class);

  $app->get('/', \App\Application\Actions\Home\HomeAction::class)->addMiddleware($PermissionMiddleware);
  $app->group('/admin', function (Group $group) {
    $group->get('/dashboard', \App\Application\Actions\Home\DashboardAction::class);
    $group->get('/actions', \App\Application\Actions\Permission\ListRouterAction::class);
    $group->get('/syncActions', \App\Application\Actions\Common\SyncRouterAction::class);
    $group->patch('/router/{id:[0-9]+}', \App\Application\Actions\Common\RouterAction::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/navigate[/{id:[0-9]+}]', \App\Application\Actions\Common\NavigateAction::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/setting[/{id:[0-9]+}]', \App\Application\Actions\Common\SettingAction::class);
    $group->map(['GET', 'POST'], '/editPassword', \App\Application\Actions\Admin\AdminInfoAction::class);
    $group->map(['GET', 'POST'], '/userBind', \App\Application\Actions\Admin\UserBindAction::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/admins[/{id:[0-9]+}]', \App\Application\Actions\Admin\AdminAction::class);
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/roles[/{id:[0-9]+}]', \App\Application\Actions\Admin\RoleAction::class);

    //文件上传
    $group->map(['GET', 'PUT', 'POST', 'DELETE'], '/files[/{id:[0-9]+}]', \App\Application\Api\Common\FilesApi::class);
  })->addMiddleware($PermissionMiddleware);
};
