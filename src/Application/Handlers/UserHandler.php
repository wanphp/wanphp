<?php

namespace App\Application\Handlers;

use Exception;
use Predis\ClientInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Wanphp\Libray\User\User;

class UserHandler
{
  /**
   * 取用户ID
   * @param Request $request
   * @param ContainerInterface $container
   * @return array
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws Exception
   */
  public static function getUser(Request $request, ContainerInterface $container): array
  {
    if (class_exists('\Wanphp\Plugins\OAuth2Authorization\Application\WePublicUserHandler')) {
      // 用户认证服务器，直接前往微信服务器获取用户信息
      $public = $container->get('Wanphp\Plugins\Weixin\Domain\PublicInterface');
      $user = $container->get('Wanphp\Plugins\Weixin\Domain\UserInterface');
      $weChatBase = $container->get('Wanphp\Libray\Weixin\WeChatBase');
      $user_id = \Wanphp\Plugins\OAuth2Authorization\Application\WePublicUserHandler::getUserId($public, $user, $weChatBase);
      return $user->get('id,name,tel', ['id' => $user_id]);
    } else {
      // 资源服务器，前往认证服务器取用户信息
      $user = $container->get(User::class);
      $redis = $container->get(ClientInterface::class);
      $queryParams = $request->getQueryParams();
      if (isset($queryParams['code']) && $queryParams['code'] != '' &&
        isset($queryParams['state']) && $queryParams['state'] != '' &&
        $redis->get($queryParams['state']) == 'state') {

        $redirectUri = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $request->getUri()->getPath();
        $access_token = $user->getOauthAccessToken($queryParams['code'], $redirectUri);
        // 通过token取用户信息
        if ($access_token) {
          return $user->getOauthUserinfo($access_token);
        } else {
          throw new Exception('用户授权码无效！');
        }
      } else {
        throw new Exception('服务器已拒绝请求');
      }
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param ContainerInterface $container
   * @return Response
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws Exception
   */
  public static function oauthRedirect(Request $request, Response $response, ContainerInterface $container): Response
  {
    if (class_exists('\Wanphp\Plugins\OAuth2Authorization\Application\WePublicUserHandler')) {
      // 用户认证服务器，直接前往微信服务器
      return \Wanphp\Plugins\OAuth2Authorization\Application\WePublicUserHandler::publicOauthRedirect($request, $response, $container->get('Wanphp\Libray\Weixin\WeChatBase'));
    } else {
      // 资源服务器，前往认证服务器
      return $container->get(User::class)->oauthRedirect($request, $response);
    }
  }
}