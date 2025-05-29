<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;

class HttpErrorHandler extends SlimErrorHandler
{
  protected function respond(): Response
  {
    $statusCode = 500;
    if ($this->exception instanceof HttpException) $statusCode = $this->exception->getCode();
    $response = $this->responseFactory->createResponse($statusCode);
    $json = json_encode(['code' => $statusCode, 'errMsg' => $this->exception->getMessage()], JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);

    return $response->withHeader('Content-Type', 'application/json');
  }
  public function logError(string $error): void
  {
    parent::logError($error);
  }
}
