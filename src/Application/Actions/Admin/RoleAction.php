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
        if (is_string($data['restricted'])) $data['restricted'] = json_decode($data['restricted'], true);
        $id = $this->role->insert($data);
        return $this->respondWithData(['id' => $id], 201);
      case  'PUT';
        $data = $this->request->getParsedBody();
        $id = (int)$this->args['id'];
        $role_id = $this->role->get('id', ['id[!]' => $id, 'name' => $data['name']]);
        if (is_numeric($role_id) && $role_id > 0) {
          return $this->respondWithError('角色已经存在');
        }
        if (is_string($data['restricted'])) $data['restricted'] = json_decode($data['restricted'], true);
        $num = $this->role->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->role->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum]);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $params = $this->request->getQueryParams();
          $where = [];

          $recordsTotal = $this->role->count('id', $where);
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $keyword = addcslashes($keyword, '*%_');
            $where['name[~]'] = $keyword;
          }

          $order = $this->getOrder();
          if ($order) $where['ORDER'] = $order;
          $recordsFiltered = $this->role->count('id', $where);
          $limit = $this->getLimit();
          if ($limit) $where['LIMIT'] = $limit;

          return $this->respondWithData([
            "draw" => $params['draw'],
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            'data' => $this->role->select('id,name,restricted[JSON]', $where)
          ]);
        } else {
          $actions = $this->router->select('id,name,route', ['route[~]' => '/admin/%']);
          $data = [
            'title' => '角色管理',
            'actions' => json_encode(array_column($actions, 'name', 'id'),JSON_UNESCAPED_UNICODE + JSON_NUMERIC_CHECK),
            'routes' => $actions
          ];

          return $this->respondView('admin/admin/rolelist.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
