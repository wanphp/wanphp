<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\ResponseEmitter\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

class ShutdownHandler
{
  /**
   * @var Request
   */
  private Request $request;

  /**
   * @var HttpErrorHandler
   */
  private HttpErrorHandler $errorHandler;

  /**
   * @var bool
   */
  private bool $displayErrorDetails;

  /**
   * ShutdownHandler constructor.
   *
   * @param Request $request
   * @param $errorHandler $errorHandler
   * @param bool $displayErrorDetails
   */
  public function __construct(Request $request, HttpErrorHandler $errorHandler, bool $displayErrorDetails)
  {
    $this->request = $request;
    $this->errorHandler = $errorHandler;
    $this->displayErrorDetails = $displayErrorDetails;
  }

  public function __invoke(): void
  {
    $error = error_get_last();
    if ($error) {
      $errorFile = $error['file'];
      $errorLine = $error['line'];
      $errorMessage = $error['message'];
      $errorType = $error['type'];


      $message = match ($errorType) {
        E_USER_ERROR => "FATAL ERROR: {$errorMessage}. on line {$errorLine} in file {$errorFile}.",
        E_USER_WARNING => "WARNING: {$errorMessage}",
        E_USER_NOTICE => "NOTICE: {$errorMessage}",
        default => "ERROR: {$errorMessage} on line {$errorLine} in file {$errorFile}.",
      };
      $this->errorHandler->logError("PATH {$this->request->getUri()->getPath()}, $message");
      if (!$this->displayErrorDetails) $message = 'An error while processing your request. Please try again later.';

      $exception = new HttpInternalServerErrorException($this->request, $message);
      $response = $this->errorHandler->__invoke($this->request, $exception, $this->displayErrorDetails, false, false);

      $responseEmitter = new ResponseEmitter();
      $responseEmitter->emit($response);
    }
  }
}
