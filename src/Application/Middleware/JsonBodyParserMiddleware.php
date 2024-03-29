<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/27
 * Time: 14:25
 */

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JsonBodyParserMiddleware implements MiddlewareInterface
{
  public function process(Request $request, RequestHandler $handler): Response
  {
    $contentType = $request->getHeaderLine('Content-Type');

    if (str_contains($contentType, 'application/json')) {
      $contents = json_decode(file_get_contents('php://input'), true);
      if (json_last_error() === JSON_ERROR_NONE) {
        //小程序获取访问令牌
        if (isset($contents['grant_type']) && $contents['grant_type'] == 'miniprogram') {
          $contents['grant_type'] = 'password';
          $contents['username'] = $contents['user'];
          $contents['password'] = $contents['code'];
          unset($contents['user']);
          unset($contents['code']);
        }
        $request = $request->withParsedBody($contents);
      }
    }

    return $handler->handle($request);
  }
}
