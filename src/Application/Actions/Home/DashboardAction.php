<?php

namespace App\Application\Actions\Home;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class DashboardAction extends \App\Application\Actions\Action
{

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    return $this->respondView('admin/common/dashboard.html');
  }
}
