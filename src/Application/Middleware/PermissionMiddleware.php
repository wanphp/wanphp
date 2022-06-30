<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/7
 * Time: 14:44
 */

namespace App\Application\Middleware;

use App\Domain\Admin\AdminInterface;
use App\Domain\Weixin\UserInterface;
use App\Repositories\Mysql\Router\PersistenceRepository;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class PermissionMiddleware implements Middleware
{
  private PersistenceRepository $persistence;
  private ContainerInterface $container;

  /**
   * @param ContainerInterface $container
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    $this->persistence = $container->get(PersistenceRepository::class);
  }

  /**
   * @param Request $request
   * @param RequestHandler $handler
   * @return Response
   * @throws BadFormatException
   * @throws ContainerExceptionInterface
   * @throws EnvironmentIsBrokenException
   * @throws LoaderError
   * @throws NotFoundExceptionInterface
   * @throws RuntimeError
   * @throws SyntaxError
   * @throws Exception
   */
  public function process(Request $request, RequestHandler $handler): Response
  {
    if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) {//已登录，验证权限
      $this->persistence->setPermission($_SESSION['role_id']);
      $routeContext = RouteContext::fromRequest($request);

      if ($this->persistence->hasRestricted($routeContext->getRoute()->getCallable())) {
        if ($request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $response = new \Slim\Psr7\Response();
          $json = json_encode(['errMsg' => '用户未获得授权，操作被拒绝！'], JSON_PRETTY_PRINT);
          $response->getBody()->write($json);
          return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else {
          return Twig::fromRequest($request)->render(new \Slim\Psr7\Response(), 'admin/error/404.html', ['message' => '用户未获得授权！']);
        }
      }

      // 绑定用户
      $admin = $this->container->get(AdminInterface::class)->get('uid,account', ['id' => $_SESSION['login_id']]);
      if (isset($admin['uid']) && $admin['uid'] > 0) {
        $user = $this->container->get(UserInterface::class)->get('headimgurl,nickname,name', ['id' => $admin['uid']]);
        $admin = array_merge($admin, $user);
      }

      $tplVars = [
        'sidebar' => $this->persistence->getSidebar(),
        'loginId' => $_SESSION['login_id'],
        'Role' => $_SESSION['role_id'],
        'loginUser' => $admin,
        'thisUri' => $request->getUri()->getScheme() . '://' . $request->getUri()->getHost()
      ];
      $request = $request->withAttribute('tplVars', $tplVars);

      return $handler->handle($request);
    } else {
      // OAuth2.0 验证
      $client_id = $request->getAttribute('oauth_client_id');
      $role_id = $request->getAttribute('oauth_admin_role_id');

      if ($client_id == 'sysManage' && $role_id) {//已登录，验证权限
        $this->persistence->setPermission($role_id);
        $routeContext = RouteContext::fromRequest($request);

        if ($this->persistence->hasRestricted($routeContext->getRoute()->getCallable())) {
          return (new OAuthServerException('未获得授权！', 401, 'Unauthorized'))->generateHttpResponse(new \Slim\Psr7\Response());
        }
        return $handler->handle($request);
      } else {
        if ($request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $response = new \Slim\Psr7\Response();
          $json = json_encode(['type' => 'reload', 'errMsg' => '用户未登录或登录超时！']);
          $response->getBody()->write($json);
          return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else {
          $code = Crypto::encrypt(session_id(), Key::loadFromAsciiSafeString($this->container->get('oauth2Config')['encryptionKey']));
          $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
          $writer = new Writer($renderer);
          $data['loginQr'] = $writer->writeString($request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . '/qrlogin?tk=' . $code);
          return Twig::fromRequest($request)->render(new \Slim\Psr7\Response(), 'admin/login.html', $data);
        }
      }
    }

  }

}
