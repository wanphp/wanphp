<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
  private $key;

  public function __construct($key)
  {
    try {
      $this->key = Key::loadFromAsciiSafeString($key);
    } catch (\Exception $e) {
      $this->key = $key;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process(Request $request, RequestHandler $handler): Response
  {
    $params = $request->getServerParams();
    if (isset($params['HTTP_SSITOKEN']) && !empty($params['HTTP_SSITOKEN'])) {
      try {
        $session_id = Crypto::decrypt($params['HTTP_SSITOKEN'], $this->key);
        session_id($session_id);
      } catch (\Exception $e) {

      }
      //session_set_cookie_params(600, '/');
    }
    session_start();

    $authorization = $params['HTTP_AUTHORIZATION'] ?? null;
    if ($authorization) {
      $request = $request->withAttribute('session', $_SESSION);
    }

    return $handler->handle($request);
  }
}
