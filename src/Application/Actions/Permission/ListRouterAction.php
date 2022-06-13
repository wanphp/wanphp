<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/7
 * Time: 16:58
 */

namespace App\Application\Actions\Permission;


use App\Domain\Common\NavigateInterface;
use App\Domain\Common\RouterInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListRouterAction extends Router
{
  private NavigateInterface $navigateRepository;

  public function __construct(LoggerInterface $logger, RouterInterface $routerRepository, NavigateInterface $navigateRepository)
  {
    parent::__construct($logger, $routerRepository);
    $this->navigateRepository = $navigateRepository;
  }

  protected function action(): Response
  {
    $navigate = $this->navigateRepository->select('*', ['ORDER' => ['sortOrder' => 'ASC']]);
    $menus = [];
    foreach ($navigate as $item) {
      $menus[$item['id']] = $item;
    }
    $actions = $this->routerRepository->select('id,navId,name,route', ['route[~]' => '/admin/%', 'ORDER' => ['sortOrder' => 'ASC']]);
    foreach ($actions as $action) {
      if ($action['navId'] > 0) $menus[$action['navId']]['sublist'][] = ['id' => $action['id'], 'name' => $action['name']];
    }

    $data = [
      'title' => '操作权限管理',
      'menus' => $menus,
      'actions' => $actions
    ];

    return $this->respondView('admin/permission/actions.html', $data);
  }

}
