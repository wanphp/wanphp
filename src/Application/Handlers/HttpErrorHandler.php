<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
  /**
   * @inheritdoc
   */
  protected function respond(): Response
  {
    $exception = $this->exception;
    $statusCode = 500;
    $error = new ActionError(
      ActionError::SERVER_ERROR,
      'An internal error has occurred while processing your request.'
    );

    if ($exception instanceof HttpException) {
      $statusCode = $exception->getCode();
      $error->setDescription($exception->getMessage());

      if ($exception instanceof HttpNotFoundException) {
        $error->setType(ActionError::RESOURCE_NOT_FOUND);
      } elseif ($exception instanceof HttpMethodNotAllowedException) {
        $error->setType(ActionError::NOT_ALLOWED);
      } elseif ($exception instanceof HttpUnauthorizedException) {
        $error->setType(ActionError::UNAUTHENTICATED);
      } elseif ($exception instanceof HttpForbiddenException) {
        $error->setType(ActionError::INSUFFICIENT_PRIVILEGES);
      } elseif ($exception instanceof HttpBadRequestException) {
        $error->setType(ActionError::BAD_REQUEST);
      } elseif ($exception instanceof HttpNotImplementedException) {
        $error->setType(ActionError::NOT_IMPLEMENTED);
      }
    }

    if (!($exception instanceof HttpException) && ($exception instanceof Throwable) && $this->displayErrorDetails) {
      $error->setDescription($exception->getMessage());
    }

    $response = $this->responseFactory->createResponse($statusCode);
    $this->logger->error($error->getDescription(), ['path' => $this->request->getUri()->getPath(), 'file' => $exception->getFile(), 'line' => $exception->getLine()]);
    $json = json_encode(['code' => $statusCode, 'errMsg' => $error->getDescription()], JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);

    return $response->withHeader('Content-Type', 'application/json');
  }
}
