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
  private WpUserInterface $user;

  /**
   * @param LoggerInterface $logger
   * @param AdminInterface $admin
   * @param RoleInterface $role
   * @param WpUserInterface $user
   */
  public function __construct(LoggerInterface $logger, AdminInterface $admin, RoleInterface $role, WpUserInterface $user)
  {
    parent::__construct($logger);
    $this->admin = $admin;
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
        if (is_numeric($id) && $id > 0) {
          return $this->respondWithError('帐号已经存在');
        }
        $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
        $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        $data['ctime'] = time();
        $data['id'] = $this->admin->insert($data);
        return $this->respondWithData($data, 201);
      case  'PUT';
        $id = (int)$this->resolveArg('id');
        $data = $this->request->getParsedBody();
        $admin_id = $this->admin->get('id', ['id[!]' => $id, 'account' => $data['account']]);
        if (is_numeric($admin_id) && $admin_id > 0) {
          return $this->respondWithError('帐号已经存在');
        }
        if (isset($data['password'])) {
          $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
          $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        }
        $num = $this->admin->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->admin->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum], 200);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $params = $this->request->getQueryParams();
          $where = [];
          // 查看选择角色
          if (isset($params['role_id']) && $params['role_id'] > 0) {
            $role_id = intval($params['role_id']);
          } else {
            $role_id = $_SESSION['role_id'] ?? -1;
          }
          $where['role_id'] = $role_id;
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

          $admins = $this->admin->select('id,uid,role_id,name,tel,account,status,lastLoginTime,lastLoginIp', $where);
          $user_id = array_filter(array_column($admins, 'uid'));
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
          if ($role_id < 0) $role = $this->role->select('id,name');
          else $role = $this->role->select('id,name', ['id' => $role_id]);
          $data = [
            'title' => '管理员管理',
            'roles' => array_column($role, 'name', 'id'),
            'role_id' => $role[0]['id'] ?? 0
          ];

          return $this->respondView('admin/admin/adminlist.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
