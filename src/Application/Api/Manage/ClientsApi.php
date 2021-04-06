<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/24
 * Time: 14:03
 */

namespace App\Application\Api\Manage;


use App\Application\Api\Api;
use App\Domain\Common\ClientsInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class ClientsApi
 * @title 客户端管理
 * @route /api/manage/clients
 * @package App\Application\Api\Manage
 */
class ClientsApi extends Api
{
  private $clients;

  public function __construct(ClientsInterface $clients)
  {
    $this->clients = $clients;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *  path="/api/manage/clients",
   *  tags={"Clients"},
   *  summary="添加客户端",
   *  operationId="addClients",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="客户端数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ClientsEntity")
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
   *  path="/api/manage/clients/{ID}",
   *  tags={"Clients"},
   *  summary="修改客户端",
   *  operationId="editClients",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="客户端ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ClientsEntity")
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
   *  path="/api/manage/clients/{ID}",
   *  tags={"Clients"},
   *  summary="删除客户端",
   *  operationId="delClients",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="ID",
   *    in="path",
   *    description="客户端ID",
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
   *         @OA\Property(property="res",@OA\Property(property="del_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/manage/clients",
   *  tags={"Clients"},
   *  summary="客户端",
   *  operationId="listClients",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",type="array",@OA\Items(ref="#/components/schemas/ClientsEntity"))
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
        $data['id'] = $this->clients->insert($data);
        return $this->respondWithData($data, 201);
        break;
      case  'PUT';
        $data = $this->request->getParsedBody();
        $num = $this->clients->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case  'DELETE';
        $delnum = $this->clients->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['del_num' => $delnum], 200);
        break;
      case 'GET';
        return $this->respondWithData($this->clients->select('*'));
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
