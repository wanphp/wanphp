<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 9:25
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use App\Domain\Common\NavigateInterface;
use App\Domain\DomainException\DomainException;
use App\Repositories\Mysql\Router\PersistenceRepository;
use Psr\Http\Message\ResponseInterface as Response;

class NavigateApi extends Api
{
  private $navigate;
  private $persistence;

  public function __construct(NavigateInterface $navigate, PersistenceRepository $persistence)
  {
    $this->navigate = $navigate;
    $this->persistence = $persistence;
  }

  /**
   * @return Response
   * @throws DomainException
   * @OA\Post(
   *  path="/api/manage/navigate",
   *  tags={"System"},
   *  summary="添加导航",
   *  operationId="addNavigate",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="导航菜单",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/NavigateEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",@OA\Property(property="id",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Put(
   *  path="/api/manage/navigate/{ID}",
   *  tags={"System"},
   *  summary="修改导航",
   *  operationId="editNavigate",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="导航菜单ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/NavigateEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="请求成功",
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
   * @OA\Delete(
   *  path="/api/manage/navigate/{ID}",
   *  tags={"System"},
   *  summary="删除导航",
   *  operationId="delNavigate",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="ID",
   *    in="path",
   *    description="导航菜单ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",@OA\Property(property="del_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/manage/navigate",
   *  tags={"System"},
   *  summary="获取导航菜单",
   *  operationId="router",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->request->getParsedBody();
        $id = $this->navigate->insert($data);
        return $this->respondWithData(['id' => $id], 201);
        break;
      case 'PUT':
        $data = $this->request->getParsedBody();
        $num = $this->navigate->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case 'DELETE':
        $delnum = $this - $this->navigate->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['del_num' => $delnum], 200);
        break;
      default:
        return $this->respondWithData(array_merge($this->persistence->getSidebar()));
    }
  }
}
