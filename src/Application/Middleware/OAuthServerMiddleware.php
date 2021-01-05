<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/15
 * Time: 10:18
 */

namespace App\Application\Middleware;


use App\Infrastructure\Database\Redis;
use App\Repositories\Mysql\Author2\AccessTokenRepository;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class OAuthServerMiddleware implements MiddlewareInterface
{
  private $redis;

  public function __construct(ContainerInterface $container, Redis $redis)
  {
    $settings = $container->get('settings');
    $redis->select($settings['authRedis']);//选择库
    $this->redis = $redis;
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
      return $handler->handle($request);
    } catch (OAuthServerException $exception) {
      return $exception->generateHttpResponse(new Response());
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return (new OAuthServerException($exception->getMessage(), 0, 'BadRequest'))
        ->generateHttpResponse(new Response());
      // @codeCoverageIgnoreEnd
    }
  }

}
