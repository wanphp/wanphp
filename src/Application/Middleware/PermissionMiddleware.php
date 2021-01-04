<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/7
 * Time: 14:44
 */

namespace App\Application\Middleware;

use App\Domain\Admin\AdminInterface;
use App\Repositories\Mysql\Router\PersistenceRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class PermissionMiddleware implements Middleware
{
  private $persistence;
  private $admin;

  public function __construct(PersistenceRepository $persistence, AdminInterface $admin)
  {
    $this->persistence = $persistence;
    $this->admin = $admin;
  }

  public function process(Request $request, RequestHandler $handler): Response
  {
    $client_id = $request->getAttribute('oauth_client_id');
    $user_id = $request->getAttribute('oauth_user_id');//可能是admin_id,或uid

    try {
      if (is_numeric($user_id)) {
        $admin = $this->admin->get('id,role_id', ['uid' => $user_id]);
        $request->withAttribute('oauth_admin_id', $admin['uid']);
      } else {
        $user_id = ltrim($user_id, 'admin_');
        $admin = $this->admin->get('uid,role_id', ['id' => $user_id]);
        $request->withAttribute('oauth_admin_id', $user_id);
        $request->withAttribute('oauth_user_id', $admin['uid']);
      }
    } catch (\App\Domain\DomainException\MedooException $e) {
      return (new OAuthServerException($e->getMessage(), 400, 'BadRequest'))->generateHttpResponse(new \Slim\Psr7\Response());
    }

    $role_id = $admin['role_id'] ?? 0;
    if ($client_id == 'sysmanage' && $role_id) {//已登录，验证权限
      $this->persistence->setPermission($role_id);
      $routeContext = RouteContext::fromRequest($request);

      if ($this->persistence->hasRestricted($routeContext->getRoute()->getCallable())) {
        return (new OAuthServerException('未获得授权！', 401, 'Unauthorized'))->generateHttpResponse(new \Slim\Psr7\Response());
      }
      $request = $request->withAttribute('sidebar', $this->persistence->getSidebar());
      return $handler->handle($request);
    } else {
      return (new OAuthServerException('未知用户请求！', 400, 'BadRequest'))->generateHttpResponse(new \Slim\Psr7\Response());
    }
  }

}
