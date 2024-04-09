<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RefererMiddleware implements MiddlewareInterface
{
  private array $referer;

  public function __construct(array $referer)
  {
    $this->referer = $referer;
  }

  public function process(Request $request, RequestHandler $handler): Response
  {
    // 验证请求的来源
    $referer = $request->getHeaderLine('Referer') ?? '';
    foreach ($this->referer as $item) {
      if (str_starts_with($referer, $item)) {
        $referer = $item;
        break;
      }
    }
    if ($referer && !in_array($referer, $this->referer)) {
      $response = new \Slim\Psr7\Response();
      $response->getBody()->write('Invalid request source');
      return $response->withHeader('Content-Type', 'text/plain')->withStatus(403);
    }
    $authorization = $request->getHeaderLine('Authorization') ?? '';
    if ($authorization) {
      $csrf = explode('.', $authorization);
      $contents = $request->getParsedBody();
      $contents['csrf_name'] = $csrf[0];
      $contents['csrf_value'] = $csrf[1];
      $request = $request->withParsedBody($contents);
    }

    $response = $handler->handle($request);

    $origin = $request->getHeaderLine('Origin') ?? '';
    if (!in_array($origin, $this->referer)) $origin = '';

    return $response->withHeader('Access-Control-Allow-Origin', $origin);
  }

}
