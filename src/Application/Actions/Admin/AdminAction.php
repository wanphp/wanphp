<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/9
 * Time: 15:06
 */

namespace App\Application\Actions\Admin;


use App\Application\Actions\Action;
use App\Domain\Admin\AdminInterface;
use App\Domain\Admin\RoleInterface;
use Medoo\Medoo;
use Overtrue\Pinyin\Pinyin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Plugins\Weixin\Domain\UserInterface;

/**
 * Class AdminAction
 * @title 管理员管理
 * @route /admin/admins
 * @package App\Application\Actions\Admin
 */
class AdminAction extends Action
{
  private RoleInterface $role;
  private AdminInterface $admin;
  private UserInterface $user;

  public function __construct(LoggerInterface $logger, AdminInterface $admin, RoleInterface $role, UserInterface $user)
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->role = $role;
    $this->user = $user;
  }

  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {

      $params = $this->request->getQueryParams();
      // 查看选择角色
      if (isset($params['role_id']) && $params['role_id'] > 0) {
        $role_id = intval($params['role_id']);
        $where = "WHERE FIND_IN_SET({$role_id},`role_id`)";
      } else {

        $role_id = $_SESSION['role_id'] ?? [];
        if ($role_id) {
          $role_where = [];
          foreach ($role_id as $id) {
            $role_where[] = "FIND_IN_SET($id,`role_id`)";
          }
          $where = 'WHERE (' . join(' OR ', $role_where) . ')';
        } else $where = 'WHERE `role_id`!=-1';
      }
      if ($_SESSION['role_id'] != -1) $where = " AND `parentId`='{$_SESSION['login_id']}'";//只显示自己添加的管理员

      $recordsTotal = $this->admin->adminCount('id', Medoo::raw($where));
      if (!empty($params['search']['value'])) {
        $keyword = trim($params['search']['value']);
        $keyword = addcslashes($keyword, '*%_');
        $where .= " AND (`name` LIKE '%{$keyword}%' OR `account` LIKE '%{$keyword}%' OR `tel` LIKE '%{$keyword}%')";
      }

      if (isset($params['order'])) foreach ($params['order'] as $param) {
        $sort = strtoupper($param['dir']);
        $where .= " ORDER BY {$params['columns'][$param['column']]['data']} {$sort}";
      }

      $admins = $this->admin->getAdminList(
        ['id', 'uid', 'role_id', 'townId', 'name', 'tel', 'account', 'status', 'lastlogintime', 'lastloginip'],
        Medoo::raw($where . " LIMIT {$params['start']}, {$params['length']}")
      );
      $user_id = array_filter(array_column($admins, 'uid'));
      // 绑定微信
      if (!empty($user_id)) {
        $users = $this->user->select('id,nickname,headimgurl', ['id' => $user_id]);
        $nickname = array_column($users, 'nickname', 'id');
        $headimgurl = array_column($users, 'headimgurl', 'id');

        foreach ($admins as &$admin) {
          $admin['weuser'] = ['nickname' => $nickname[$admin['uid']] ?? '', 'headimgurl' => $headimgurl[$admin['uid']] ?? ''];
        }
      }

      $data = [
        "draw" => $params['draw'],
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $this->admin->adminCount('id', Medoo::raw($where)),
        'data' => $admins
      ];

      $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
      $this->response->getBody()->write($json);
      return $this->respond(200);
    } else {
      $role_id = $_SESSION['role_id'] ?? 0;
      if ($role_id == -1) $role = $this->role->select('id,name');
      else $role = $this->role->select('id,name', ['id' => $role_id]);
      $data = [
        'title' => '管理员管理',
        'roles' => array_column($role, 'name', 'id'),
        'role_id' => $role[0]['id'] ?? 0
      ];

      return $this->respondView('admin/admin/adminlist.html', $data);
    }
  }
}
