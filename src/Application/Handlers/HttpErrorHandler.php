<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;

class HttpErrorHandler extends SlimErrorHandler
{
  protected function respond(): Response
  {
    $response = $this->responseFactory->createResponse($this->exception->getCode());
    $json = json_encode(['code' => $this->exception->getCode(), 'errMsg' => $this->exception->getMessage()], JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);

    return $response->withHeader('Content-Type', 'application/json');
  }
  public function logError(string $error): void
  {
    parent::logError($error);
  }
}
