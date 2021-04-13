<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 15:08
 */

namespace App\Application\Api\Manage\Admin;


use App\Application\Api\Api;
use App\Domain\Admin\RoleInterface;
use App\Domain\Common\RouterInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class RoleApi
 * @title 管理员角色
 * @route /api/manage/admin/role
 * @package App\Application\Api\Manage
 */
class RoleApi extends Api
{
  private $role;
  private $router;

  public function __construct(RoleInterface $role, RouterInterface $router)
  {
    $this->role = $role;
    $this->router = $router;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *  path="/api/manage/admin/role",
   *  tags={"AdminRole"},
   *  summary="添加管理员角色",
   *  operationId="addAdminRole",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="管理员角色数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/RoleEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="添加成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",@OA\Property(property="id",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Put(
   *  path="/api/manage/admin/role/{ID}",
   *  tags={"AdminRole"},
   *  summary="修改管理员角色",
   *  operationId="editAdminRole",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="管理员角色ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/RoleEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="更新成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",@OA\Property(property="up_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Delete(
   *  path="/api/manage/admin/role/{ID}",
   *  tags={"AdminRole"},
   *  summary="删除管理员角色",
   *  operationId="delAdminRole",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="ID",
   *    in="path",
   *    description="管理员角色ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(
   *    response="200",
   *    description="删除成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",@OA\Property(property="del_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/manage/admin/role",
   *  tags={"AdminRole"},
   *  summary="管理员角色",
   *  operationId="listAdminRole",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",type="array",@OA\Items(ref="#/components/schemas/RoleEntity"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case  'POST';
        $data = $this->request->getParsedBody();
        $id = $this->role->insert($data);
        return $this->respondWithData(['id' => $id], 201);
        break;
      case  'PUT';
        $data = $this->request->getParsedBody();
        $num = $this->role->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case  'DELETE';
        $delnum = $this->role->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['del_num' => $delnum], 200);
        break;
      case 'GET';
        $router = $this->router->select('id,name', ['ORDER' => ['sortOrder' => 'ASC']]);
        $roles = $this->role->select('*');
        return $this->respondWithData(['roles' => $roles, 'router' => $router]);
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
