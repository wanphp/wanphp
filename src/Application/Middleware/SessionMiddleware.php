<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
  private Key $key;

  /**
   * @param $key
   * @throws BadFormatException
   * @throws EnvironmentIsBrokenException
   */
  public function __construct($key)
  {
    $this->key = Key::loadFromAsciiSafeString($key);
  }

  /**
   * @param Request $request
   * @param RequestHandler $handler
   * @return Response
   * @throws EnvironmentIsBrokenException
   * @throws WrongKeyOrModifiedCiphertextException
   */
  public function process(Request $request, RequestHandler $handler): Response
  {
    $params = $request->getServerParams();
    $queryParams = $request->getQueryParams();
    $ssiToken = $params['HTTP_SSITOKEN'] ?? ($queryParams['tk'] ?? '');
    if ($ssiToken != '') {
      $session_id = Crypto::decrypt($ssiToken, $this->key);
      if ($session_id) session_id($session_id);
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
