<?php

namespace App\Application\Actions\Admin;

use App\Domain\Admin\AdminInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AdminSearchAction extends \App\Application\Actions\Action
{
  private AdminInterface $admin;

  public function __construct(LoggerInterface $logger, AdminInterface $admin)
  {
    parent::__construct($logger);
    $this->admin = $admin;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $params = $this->request->getQueryParams();
    $where = ['role_id' => $params['role_id'], 'LIMIT' => [0, 10]];

    if (!empty($params['kw'])) {
      $keyword = trim($params['kw']);
      $keyword = addcslashes($keyword, '*%_');
      $where['account[~]'] = $keyword;
    }

    $admins = $this->admin->select('id, account(name)', $where);
    return $this->respondWithData($admins);
  }
}
