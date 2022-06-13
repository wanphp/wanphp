<?php

namespace App\Application\Actions\Admin;

use App\Domain\Admin\AdminInterface;
use Medoo\Medoo;
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
    $where = "WHERE FIND_IN_SET({$params['role_id']},`role_id`)";

    if (!empty($params['kw'])) {
      $keyword = trim($params['kw']);
      $keyword = addcslashes($keyword, '*%_');
      $where .= " AND `account` LIKE '%{$keyword}%'";
    }

    $admins = $this->admin->getAdminList(
      ['id', 'account(name)'],
      Medoo::raw($where . " LIMIT 0, 20")
    );
    return $this->respondWithData($admins);
  }
}