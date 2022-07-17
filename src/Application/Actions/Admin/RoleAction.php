<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/10
 * Time: 16:08
 */

namespace App\Application\Actions\Admin;


use App\Application\Actions\Action;
use App\Domain\Admin\RoleInterface;
use App\Domain\Common\RouterInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class ListRoleAction
 * @title 角色管理
 * @route /admin/roles
 * @package App\Application\Actions\Admin
 */
class RoleAction extends Action
{
  private RoleInterface $roleRepository;
  private RouterInterface $routerRepository;

  public function __construct(LoggerInterface $logger, RoleInterface $roleRepository, RouterInterface $router)
  {
    parent::__construct($logger);
    $this->roleRepository = $roleRepository;
    $this->routerRepository = $router;
  }

  protected function action(): Response
  {
    $actions = $this->routerRepository->select('id,name,route', ['route[~]' => '/admin/%']);
    $data = [
      'title' => '角色管理',
      'roles' => $this->roleRepository->select('id,name,restricted[JSON]'),
      'actions' => array_column($actions, 'name', 'id'),
      'routes' => $actions
    ];

    return $this->respondView('admin/admin/rolelist.html', $data);
  }
}
