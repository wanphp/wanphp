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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

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
  private ContainerInterface $container;

  /**
   * @param LoggerInterface $logger
   * @param AdminInterface $admin
   * @param RoleInterface $role
   * @param ContainerInterface $container
   */
  public function __construct(LoggerInterface $logger, AdminInterface $admin, RoleInterface $role, ContainerInterface $container)
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->role = $role;
    $this->container = $container;
  }

  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
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
          // 查看选择角色
          if (isset($params['role_id']) && $params['role_id'] > 0) {
            $role_id = intval($params['role_id']);
            $where = "WHERE REPLACE(`role_id`, ',', '][') LIKE '%[\"$role_id\"]%'";
          } else {
            $role_id = $_SESSION['role_id'] ?? [];
            if ($role_id) {
              $role_where = [];
              foreach ($role_id as $id) {
                $role_where[] = "REPLACE(`role_id`, ',', '][') LIKE '%[\"$id\"]%'";
              }
              $where = 'WHERE (' . join(' OR ', $role_where) . ')';
            } else $where = "WHERE REPLACE(`role_id`, ',', '][') LIKE '%[\"-1\"]%'";
          }
          if (!in_array(-1, $_SESSION['role_id'])) $where = " AND `parentId`='{$_SESSION['login_id']}'";//只显示自己添加的管理员

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
            ['id', 'uid', 'role_id[JSON]', 'name', 'tel', 'account', 'status', 'lastlogintime', 'lastloginip'],
            Medoo::raw($where . " LIMIT {$params['start']}, {$params['length']}")
          );
          $user_id = array_filter(array_column($admins, 'uid'));
          // 绑定微信
          if (!empty($user_id)) {
            $users = [];
            if ($this->container->has('Wanphp\Plugins\Weixin\Domain\UserInterface')) {
              $user = $this->container->get('Wanphp\Plugins\Weixin\Domain\UserInterface');
              foreach ($user->select('id,nickname,headimgurl', ['id' => $user_id]) as $item) {
                $users[$item['id']] = ['nickname' => $item['nickname'], 'headimgurl' => $item['headimgurl']];
              }
            }
            if ($this->container->has('Wanphp\Libray\User\User')) {
              $user = $this->container->get('Wanphp\Libray\User\User');
              foreach ($user->getUsers($user_id) as $item) {
                $users[$item['id']] = ['nickname' => $item['nickname'], 'headimgurl' => $item['headimgurl']];
              }
            }

            if ($users) foreach ($admins as &$admin) {
              $admin['weuser'] = ['nickname' => $users[$admin['uid']]['nickname'] ?? '', 'headimgurl' => $users[$admin['uid']]['headimgurl'] ?? ''];
            }
          }

          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $this->admin->adminCount('id', Medoo::raw($where)),
            'data' => $admins
          ];

          return $this->respondWithData($data);
        } else {
          $role_id = $_SESSION['role_id'];
          if (in_array(-1, $role_id)) $role = $this->role->select('id,name');
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
