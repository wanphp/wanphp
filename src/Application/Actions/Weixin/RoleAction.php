<?php

namespace App\Application\Actions\Weixin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Plugins\Weixin\Domain\UserRoleInterface;

/**
 * Class RoleAction
 * @title 用户角色
 * @route /admin/weixin/roles
 * @package App\Application\Actions\Weixin
 */
class RoleAction extends \App\Application\Actions\Action
{
  private UserRoleInterface $userRole;

  public function __construct(LoggerInterface $logger, UserRoleInterface $userRole)
  {
    parent::__construct($logger);
    $this->userRole = $userRole;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
      return $this->respondWithData(['data' => $this->userRole->select('*')]);
    } else {
      $data = [
        'title' => '用户角色管理'
      ];

      return $this->respondView('admin/weixin/roles.html', $data);
    }

  }
}