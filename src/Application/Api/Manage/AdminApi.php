<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 14:58
 */

namespace App\Application\Api\Manage;


use App\Application\Api\Api;
use App\Domain\Admin\AdminInterface;
use App\Domain\DomainException\DomainException;
use Psr\Http\Message\ResponseInterface as Response;

class AdminApi extends Api
{
  private $admin;

  public function __construct(AdminInterface $admin)
  {
    $this->admin = $admin;
  }

  /**
   * @return Response
   * @throws DomainException
   * @OA\Post(
   *  path="/api/manage/admin",
   *  tags={"Admin"},
   *  summary="添加管理员",
   *  operationId="addAdmin",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="管理员数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/AdminEntity")
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
   *  path="/api/manage/admin/{ID}",
   *  tags={"Admin"},
   *  summary="修改管理员",
   *  operationId="editAdmin",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="管理员ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/AdminEntity")
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
   * @OA\Delete(
   *  path="/api/manage/admin/{ID}",
   *  tags={"Admin"},
   *  summary="删除管理员",
   *  operationId="delAdmin",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="ID",
   *    in="path",
   *    description="管理员ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(
   *    response="204",
   *    description="删除成功",
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
   *  path="/api/manage/admin",
   *  tags={"Admin"},
   *  summary="管理员",
   *  operationId="listAdmin",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",type="array",@OA\Items(ref="#/components/schemas/AdminEntity"))
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
        $data['client_secret'] = md5(uniqid(rand(), true));
        $id = $this->admin->insert($data);
        return $this->respondWithData(['id' => $id], 201);
        break;
      case  'PUT';
        $data = $this->request->getParsedBody();
        $num = $this->admin->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case  'DELETE';
        $delnum = $this->admin->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['del_num' => $delnum], 204);
        break;
      case 'GET';
        return $this->respondWithData($this->admin->select('*'));
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
