<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/15
 * Time: 10:18
 */

namespace App\Application\Middleware;


use App\Domain\Admin\AdminInterface;
use App\Repositories\Mysql\Author2\AccessTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class OAuthServerMiddleware implements MiddlewareInterface
{
  private $redis;
  private $admin;

  public function __construct(ContainerInterface $container)
  {
    $settings = $container->get('settings');
    $this->redis = new Client($container->get('redis'));
    $this->redis->select($settings['authRedis']);//选择库
    $this->admin = $container->get(AdminInterface::class);
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    // Init our repositories
    $accessTokenRepository = new AccessTokenRepository($this->redis);

    // 授权服务器分发的公钥
    $publicKeyPath = realpath('../') . '/var/conf/key/public.key';

    // Setup the authorization server
    $server = new ResourceServer(
      $accessTokenRepository,
      $publicKeyPath
    );
    try {
      $request = $server->validateAuthenticatedRequest($request);
      $client_id = $request->getAttribute('oauth_client_id');
      $user_id = $request->getAttribute('oauth_user_id');
      //系统管理
      if ($client_id == 'sysmanage'){
        try {
          if (is_numeric($user_id)) { //微信扫码登录
            $admin = $this->admin->get('id,role_id', ['uid' => $user_id]);
            $request = $request->withAttribute('oauth_admin_id', $admin['uid']);
          } else { //账号密码登录
            $user_id = ltrim($user_id, 'admin_');
            $admin = $this->admin->get('uid,role_id', ['id' => $user_id]);
            $request = $request->withAttribute('oauth_user_id', $admin['uid']);
            $request = $request->withAttribute('oauth_admin_id', $user_id);
          }
          $request = $request->withAttribute('oauth_admin_role_id', $admin['role_id']);
        } catch (\Exception $e) {
          return (new OAuthServerException($e->getMessage(), 400, 'BadRequest'))->generateHttpResponse(new \Slim\Psr7\Response());
        }
      }

      return $handler->handle($request);
    } catch (OAuthServerException $exception) {
      return $exception->generateHttpResponse(new Response());
      // @codeCoverageIgnoreStart
    } catch (\Exception $exception) {
      return (new OAuthServerException($exception->getMessage(), 0, 'BadRequest'))
        ->generateHttpResponse(new Response());
      // @codeCoverageIgnoreEnd
    }
  }

}
