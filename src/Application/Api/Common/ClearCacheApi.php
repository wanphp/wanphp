<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/21
 * Time: 16:34
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ClearCacheApi extends Api
{
  private ClientInterface $redis;

  public function __construct(ClientInterface $redis)
  {
    $this->redis = $redis;
  }

  /**
   * @return Response
   * @OA\Get(
   *  path="/api/clearCache/{DB}",
   *  tags={"System"},
   *  summary="清除缓存",
   *  operationId="clearCache",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="DB",
   *    in="path",
   *    description="指定清除缓存库",
   *    required=true,
   *    @OA\Schema(format="int32",type="integer")
   *  ),
   *  @OA\Response(response="200",description="用户更新成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    $db = $this->args['db'] ?? 1;
    $this->redis->select($db);
    $this->redis->flushdb();
    return $this->respondWithData(['msg' => 'OK!']);
  }
}
