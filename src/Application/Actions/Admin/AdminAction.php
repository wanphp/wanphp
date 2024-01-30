<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/9
 * Time: 15:06
 */

namespace App\Application\Actions\Admin;


use App\Application\Actions\Action;
use App\Domain\Admin\AdminGroupInterface;
use App\Domain\Admin\AdminInterface;
use App\Domain\Admin\RoleInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\WpUserInterface;

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
  private AdminGroupInterface $group;
  private WpUserInterface $user;

  /**
   * @param LoggerInterface $logger
   * @param AdminInterface $admin
   * @param AdminGroupInterface $group
   * @param RoleInterface $role
   * @param WpUserInterface $user
   */
  public function __construct(LoggerInterface $logger, AdminInterface $admin, AdminGroupInterface $group, RoleInterface $role, WpUserInterface $user)
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->group = $group;
    $this->role = $role;
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case  'POST';
        $data = $this->request->getParsedBody();
        $id = $this->admin->get('id', ['account' => $data['account']]);
        if (is_numeric($id) && $id > 0) return $this->respondWithError('帐号已经存在');
        if ($data['password'] == '') return $this->respondWithError('帐号密码不能为空！');
        $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
        $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        $data['ctime'] = time();
        $data['id'] = $this->admin->insert($data);
        return $this->respondWithData($data, 201);
      case  'PUT';
        $id = (int)$this->resolveArg('id');
        $data = $this->request->getParsedBody();
        $admin_id = $this->admin->get('id', ['id[!]' => $id, 'account' => $data['account']]);
        if (is_numeric($admin_id) && $admin_id > 0) return $this->respondWithError('帐号已经存在');
        if (isset($data['password'])) {
          $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
          $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        }
        $num = $this->admin->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $id = $this->resolveArg('id');
        if ($_SESSION['login_id'] == $id) return $this->respondWithError('不能删除自己');
        $delNum = $this->admin->delete(['id' => $id]);
        return $this->respondWithData(['delNum' => $delNum], 200);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $params = $this->request->getQueryParams();
          $where = [];
          // 查看选择角色
          if (isset($params['role_id']) && $params['role_id'] > 0) {
            $where['role_id'] = intval($params['role_id']);
          } else if ($_SESSION['role_id'] > 0) {
            $where['role_id'] = $_SESSION['role_id'];
          }
          if (isset($params['groupId']) && $params['groupId'] > 0) $where['groupId'] = intval($params['groupId']);
          if ($_SESSION['role_id'] != -1) $where['parentId'] = $_SESSION['login_id'];//只显示自己添加的管理员

          $recordsTotal = $this->admin->count('id', $where);
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $keyword = addcslashes($keyword, '*%_');
            $where['OR'] = [
              'account[~]' => $keyword,
              'name[~]' => $keyword,
              'tel[~]' => $keyword
            ];
          }

          $order = $this->getOrder();
          if ($order) $where['ORDER'] = $order;
          $recordsFiltered = $this->admin->count('id', $where);
          $limit = $this->getLimit();
          if ($limit) $where['LIMIT'] = $limit;

          $admins = $this->admin->select('id,uid,role_id,groupId,name,tel,account,status,lastLoginTime,lastLoginIp', $where);
          $user_id = array_unique(array_column($admins, 'uid'));
          // 绑定微信
          if (!empty($user_id)) {
            $users = [];
            foreach ($this->user->getUsers($user_id) as $item) $users[$item['id']] = ['nickname' => $item['nickname'], 'headimgurl' => $item['headimgurl']];
            if ($users) foreach ($admins as &$admin) {
              $admin['weuser'] = ['nickname' => $users[$admin['uid']]['nickname'] ?? '', 'headimgurl' => $users[$admin['uid']]['headimgurl'] ?? ''];
            }
          }

          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            'data' => $admins
          ];

          return $this->respondWithData($data);
        } else {
          $role_id = $_SESSION['role_id'];
          // id越大，权限越少
          if ($role_id < 0) $role = $this->role->select('id,name');
          else $role = $this->role->select('id,name', ['id[>]' => $role_id]);
          $group = $this->group->select('id,name', ['ORDER' => ['displayOrder' => 'ASC']]);
          $data = [
            'title' => '管理员管理',
            'roles' => array_column($role, 'name', 'id'),
            'group' => array_column($group, 'name', 'id'),
            'role_id' => $role[0]['id'] ?? 0
          ];

          return $this->respondView('admin/admin/adminlist.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
