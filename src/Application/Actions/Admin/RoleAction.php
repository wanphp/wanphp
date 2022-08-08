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
  private RoleInterface $role;
  private RouterInterface $router;

  public function __construct(LoggerInterface $logger, RoleInterface $role, RouterInterface $router)
  {
    parent::__construct($logger);
    $this->role = $role;
    $this->router = $router;
  }

  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case  'POST';
        $data = $this->request->getParsedBody();
        $id = $this->role->get('id', ['name' => $data['name']]);
        if ($id) {
          return $this->respondWithError('角色已经存在');
        }
        $id = $this->role->insert($data);
        return $this->respondWithData(['id' => $id], 201);
      case  'PUT';
        $data = $this->request->getParsedBody();
        $id = (int)$this->args['id'];
        $role_id = $this->role->get('id', ['id[!]' => $id, 'name' => $data['name']]);
        if (is_numeric($role_id) && $role_id > 0) {
          return $this->respondWithError('角色已经存在');
        }
        $num = $this->role->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->role->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum]);
      case 'GET';
        $actions = $this->router->select('id,name,route', ['route[~]' => '/admin/%']);
        $data = [
          'title' => '角色管理',
          'roles' => $this->role->select('id,name,restricted[JSON]'),
          'actions' => array_column($actions, 'name', 'id'),
          'routes' => $actions
        ];

        return $this->respondView('admin/admin/rolelist.html', $data);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
