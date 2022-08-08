<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/23
 * Time: 9:45
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use App\Application\Api\Api;
use App\Domain\Common\NavigateInterface;
use App\Domain\Common\RouterInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class RouterAction extends Action
{
  private RouterInterface $router;
  private NavigateInterface $navigate;

  public function __construct(LoggerInterface $logger, RouterInterface $router, NavigateInterface $navigate)
  {
    parent::__construct($logger);
    $this->router = $router;
    $this->navigate = $navigate;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'PATCH':
        $data = $this->request->getParsedBody();
        $num = $this->router->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['upNum' => $num], 201);
      case 'GET':
        $navigate = $this->navigate->select('*', ['ORDER' => ['sortOrder' => 'ASC']]);
        $menus = [];
        foreach ($navigate as $item) {
          $menus[$item['id']] = $item;
        }
        $routes = $this->router->select('id,navId,name,route', ['ORDER' => ['navId' => 'ASC', 'sortOrder' => 'ASC']]);
        foreach ($routes as $action) {
          if ($action['navId'] > 0) $menus[$action['navId']]['children'][] = $action;
        }
        $menus = array_merge($menus);
        return $this->respondWithData(['menus' => $menus, 'routes' => $routes ?? []]);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
