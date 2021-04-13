<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/9
 * Time: 17:35
 */

namespace App\Repositories\Mysql\Router;

use Predis\ClientInterface;
use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\NavigateInterface;
use App\Domain\Common\RouterInterface;

class PersistenceRepository
{
  private $db;
  private $redis;
  private $permission = [];//授权
  private $restricted = [];//限制

  public function __construct(Database $database, ClientInterface $redis)
  {
    $this->db = $database;
    $this->redis = $redis;
  }

  public function setPermission($role_id)
  {
    $authority = $this->redis->get('authority_' . $role_id);
    if (!$authority) {
      $routers = $this->db->select(RouterInterface::TABLENAME, ['id', 'navId', 'name', 'route', 'callable'], ['ORDER' => ['sortOrder' => 'ASC']]);
      if ($routers) {
        //角色限制权限
        if ($role_id > 0) {
          $role = $this->db->get('role', ['restricted'], ['id' => $role_id]);
          if (isset($role['restricted'])) {
            $restricted = explode(',', $role['restricted']);
            foreach ($routers as $action) {
              if (in_array($action['id'], $restricted)) {
                $this->restricted[] = $action['callable'];
              } else {
                $this->permission[$action['navId']][] = ['route' => $action['route'], 'name' => $action['name']];
              }
            }
          } else {//未找到角色
            $this->permission = [];
            $this->restricted = array_column($routers, 'callable');
          }
        }
        if ($role_id == 0) {//未配置角色限制所有权限
          $this->permission = [];
          $this->restricted = array_column($routers, 'callable');
        }
        if ($role_id < 0) {//超级管理员不限制权限
          foreach ($routers as $action) {
            $this->permission[$action['navId']][] = ['route' => $action['route'], 'name' => $action['name']];
          }
          $this->restricted = [];
        }

        $this->redis->set('authority_' . $role_id, json_encode(['permission' => $this->permission, 'restricted' => $this->restricted]));
      }
    } else {
      $authority = json_decode($authority, true);
      $this->permission = $authority['permission'];
      $this->restricted = $authority['restricted'];
    }
  }

  public function getSidebar()
  {
    $sidebar = [];
    $navigate = $this->redis->get('navigate');
    if (!$navigate) {
      $navigate = $this->db->select(NavigateInterface::TABLENAME, ['id', 'icon', 'name'], ['ORDER' => ['sortOrder' => 'ASC']]);
      $this->redis->set('navigate', json_encode($navigate));
    } else {
      $navigate = json_decode($navigate, true);
    }
    if ($navigate) foreach ($navigate as $item) {
      $sidebar[$item['id']] = ['icon' => $item['icon'], 'name' => $item['name'], 'children' => $this->permission[$item['id']] ?? []];
    }
    return $sidebar;
  }

  public function hasRestricted($callable)
  {
    return in_array($callable, $this->restricted);
  }
}
