<?php

namespace App\Application\Actions\Admin;


use App\Application\Actions\Action;
use App\Domain\Admin\AdminGroupInterface;
use App\Domain\Admin\AdminInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class GroupAction
 * @title 管理员分组
 * @route /admin/group
 * @package App\Application\Actions\Admin
 */
class GroupAction extends Action
{
  private AdminGroupInterface $group;
  private AdminInterface $admin;

  public function __construct(LoggerInterface $logger, AdminGroupInterface $group, AdminInterface $admin)
  {
    parent::__construct($logger);
    $this->group = $group;
    $this->admin = $admin;
  }

  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case  'POST';
        $data = $this->request->getParsedBody();
        $id = $this->group->get('id', ['name' => $data['name']]);
        if ($id) return $this->respondWithError('分组已经存在');
        $id = $this->group->insert($data);
        return $this->respondWithData(['id' => $id], 201);
      case  'PUT';
        $data = $this->request->getParsedBody();
        $id = $this->resolveArg('id');
        $group_id = $this->group->get('id', ['id[!]' => $id, 'name' => $data['name']]);
        if (is_numeric($group_id) && $group_id > 0) return $this->respondWithError('分组已经存在');
        $num = $this->group->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->group->delete(['id' => $this->args['id']]);
        $this->admin->update(['groupId' => 0], ['groupId' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum]);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          return $this->respondWithData([
            'data' => $this->group->select('id,name,description,displayOrder')
          ]);
        } else {
          $data = [
            'title' => '管理员分组管理'
          ];

          return $this->respondView('admin/admin/groupList.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
