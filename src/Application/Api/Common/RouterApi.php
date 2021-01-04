<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/23
 * Time: 9:45
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use App\Domain\Common\NavigateInterface;
use App\Domain\Common\RouterInterface;
use App\Domain\DomainException\DomainException;
use Psr\Http\Message\ResponseInterface as Response;

class RouterApi extends Api
{
  private $router;
  private $navigate;

  public function __construct(RouterInterface $router, NavigateInterface $navigate)
  {
    $this->router = $router;
    $this->navigate = $navigate;
  }

  /**
   * @return Response
   * @throws DomainException
   * @OA\Patch(
   *  path="/api/router/{ID}",
   *  tags={"System"},
   *  summary="修改路由",
   *  operationId="editRouter",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="路由ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(
   *         title="Route",
   *         @OA\Property(property="nav_id",type="integer",description="所在导航菜单"),
   *         @OA\Property(property="display_order",type="integer",description="排序")
   *       )
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="更新成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",@OA\Property(property="up_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/router",
   *  tags={"System"},
   *  summary="系统路由",
   *  operationId="router",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'PATCH':
        $data = $this->request->getParsedBody();
        $num = $this->router->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case 'GET':
        $navigate = $this->navigate->select('*', ['ORDER' => ['display_order' => 'ASC']]);
        foreach ($navigate as $item) {
          $menus[$item['id']] = $item;
        }
        $routes = $this->router->select('id,nav_id,name,route', ['ORDER' => ['display_order' => 'ASC']]);
        foreach ($routes as $action) {
          if ($action['nav_id'] > 0) $menus[$action['nav_id']]['sublist'][] = ['id' => $action['id'], 'name' => $action['name']];
        }

        return $this->respondWithData(['menus' => $menus ?? [], 'actions' => $routes ?? []]);
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
