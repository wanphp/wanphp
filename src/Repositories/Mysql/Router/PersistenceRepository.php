<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/9
 * Time: 17:35
 */

namespace App\Repositories\Mysql\Router;

use App\Repositories\Mysql\Admin\RoleRepository;
use Exception;
use Predis\ClientInterface;
use Wanphp\Libray\Mysql\Database;

class PersistenceRepository
{
  private RouterRepository $routerRepository;
  private RoleRepository $roleRepository;
  private NavigateRepository $navigateRepository;
  private ClientInterface $redis;
  private array $permission = [];//授权
  private array $restricted = [];//限制

  public function __construct(Database $database, ClientInterface $redis)
  {
    $this->routerRepository = new RouterRepository($database);
    $this->roleRepository = new RoleRepository($database);
    $this->navigateRepository = new NavigateRepository($database);
    $this->redis = $redis;
  }

  /**
   * @param int $role_id
   * @return void
   * @throws Exception
   */
  public function setPermission(int $role_id)
  {
    $cacheKey = 'authority_' . $role_id;
    $authority = $this->redis->get($cacheKey);
    if (!$authority) {
      $routers = $this->routerRepository->select('id,navId,name,route,callable', ['route[~]' => '/admin/%', 'ORDER' => ['sortOrder' => 'ASC']]);
      if ($routers) {
        //角色限制权限
        if ($role_id) {
          if ($role_id < 0) {
            //超级管理员不限制权限
            foreach ($routers as $action) {
              $this->permission[$action['navId']][] = ['route' => $action['route'], 'name' => $action['name']];
            }
            $this->restricted = [];
          } else {
            $restricted = $this->roleRepository->get('restricted[JSON]', ['id' => $role_id]);
            if ($restricted) {
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
        } else {//未配置角色,限制所有权限
          $this->permission = [];
          $this->restricted = array_column($routers, 'callable');
        }

        $this->redis->set($cacheKey, json_encode(['permission' => $this->permission, 'restricted' => $this->restricted]));
      }
    } else {
      $authority = json_decode($authority, true);
      $this->permission = $authority['permission'];
      $this->restricted = $authority['restricted'];
    }
  }

  /**
   * @throws Exception
   */
  public function getSidebar(): array
  {
    $sidebar = [];
    $navigate = $this->redis->get('navigate');
    if (!$navigate) {
      $navigate = $this->navigateRepository->select('id,icon,name', ['ORDER' => ['sortOrder' => 'ASC']]);
      $this->redis->set('navigate', json_encode($navigate));
    } else {
      $navigate = json_decode($navigate, true);
    }
    if ($navigate) foreach ($navigate as $item) {
      if (isset($this->permission[$item['id']])) $sidebar[$item['id']] = ['icon' => $item['icon'], 'name' => $item['name'], 'children' => $this->permission[$item['id']]];
    }
    return $sidebar;
  }

  public function hasRestricted($callable): bool
  {
    return in_array($callable, $this->restricted);
  }
}
