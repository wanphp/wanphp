<?php

namespace App\Application\Handlers;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Wanphp\Libray\Slim\WpUserInterface;

class UserHandler
{
  /**
   * 取用户信息
   * @param Request $request
   * @param WpUserInterface $user
   * @return array
   * @throws Exception
   */
  public static function getUser(Request $request, WpUserInterface $user): array
  {
    $queryParams = $request->getQueryParams();
    if (isset($queryParams['code']) && $queryParams['code'] != '' &&
      isset($queryParams['state']) && $queryParams['state'] != '' &&
      $_SESSION[$queryParams['state']] == $queryParams['state']) {
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

  /**
   * @param Request $request
   * @param Response $response
   * @param WpUserInterface $user
   * @return Response
   * @throws Exception
   */
  public static function oauthRedirect(Request $request, Response $response, WpUserInterface $user): Response
  {
    $queryParams = $request->getQueryParams();
    $state = bin2hex(random_bytes(8));
    $_SESSION[$state] = $state;
    if (!isset($queryParams['state'])) $request = $request->withQueryParams(['state' => $state]);
    return $user->oauthRedirect($request, $response);
  }
}
