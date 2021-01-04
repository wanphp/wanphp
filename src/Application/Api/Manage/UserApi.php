<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 15:48
 */

namespace App\Application\Api\Manage;


use App\Application\Api\Api;
use App\Domain\DomainException\DomainException;
use App\Domain\Weixin\UserInterface;
use App\Domain\Weixin\UserRoleInterface;
use Psr\Http\Message\ResponseInterface as Response;

class UserApi extends Api
{
  private $user;
  private $userRole;

  public function __construct(UserInterface $user, UserRoleInterface $userRole)
  {
    $this->user = $user;
    $this->userRole = $userRole;
  }

  /**
   * @return Response
   * @throws DomainException
   * @OA\Patch(
   *  path="/api/manage/users/{ID}",
   *  tags={"User"},
   *  summary="更新用户，管理员操作",
   *  operationId="editUser",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="用户ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *  @OA\RequestBody(
   *    description="指定更新用户数据",
   *    required=true,
   *    @OA\MediaType(
   *      mediaType="application/json",
   *      @OA\Schema(ref="#/components/schemas/UserEntity"),
   *      example={"name": "", "tel": null, "address": "", "integral": "0", "cash_back": "0.00", "money": "0.00"}
   *    )
   *  ),
   *  @OA\Response(
   *    response="201",
   *    description="用户更新成功",
   *  @OA\JsonContent(
   *     allOf={
   *      @OA\Schema(ref="#/components/schemas/Success"),
   *      @OA\Schema(
   *        @OA\Property(property="res", @OA\Property(property="up_num",type="integer",description="更新数量"))
   *      )
   *     }
   *   )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *   path="/api/manage/users/{ID}",
   *   tags={"User"},
   *   summary="查看用户信息，管理员查看",
   *   operationId="loadUser",
   *   security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="用户ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\Response(
   *    response="200",
   *    description="用户信息",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",ref="#/components/schemas/UserEntity")
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *   path="/api/manage/users",
   *   tags={"User"},
   *   summary="用户信息列表，管理获取",
   *   operationId="ListUsers",
   *   security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="pageSize",
   *    in="query",
   *    description="每页返回数量",
   *    @OA\Schema(format="int64",type="integer",default=10)
   *  ),
   *  @OA\Parameter(
   *    name="page",
   *    in="query",
   *    description="页码",
   *    @OA\Schema(format="int64",type="integer",default=1)
   *  ),
   *  @OA\Parameter(
   *    name="keyword",
   *    in="query",
   *    description="关键词",
   *    required=false,
   *    @OA\Schema(type="string")
   *  ),
   *   @OA\Response(
   *    response="200",
   *    description="用户信息",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="res",example={
  "id": "",
  "user": "用户",
  "role": "用色",
  "name": "Name",
  "tel": "Tel"
  })
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
      case 'PATCH':
        $data = $this->request->getParsedBody();
        if (empty($data)) return $this->respondWithError('无用户数据');
        $num = $this->user->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['up_num' => $num], 201);
        break;
      case 'GET':
        $id = $this->args['id'] ?? 0;
        if ($id > 0) {
          $user = $this->user->get('*', ['id' => $id]);
          return $this->respondWithData($user);
        }

        $userRoles = $this->userRole->select('id,name', ['ORDER' => ['display_order' => 'ASC']]);
        $roles = array_column($userRoles, 'name', 'id');

        $where = [];
        if (!empty($_GET['keyword'])) {
          $keyword = trim($_GET['keyword']);
          $where['OR'] = [
            'name[~]' => $keyword,
            'nickname[~]' => $keyword,
            'tel[~]' => $keyword
          ];
        }
        $where['ORDER'] = ["id" => "DESC"];
        $users = $this->user->select('id,nickname,headimgurl,name,tel,role_id', $where);

        //格式化数据
        $datas = [];
        foreach ($users as $user) {
          $datas[] = [
            'id' => $user['id'],
            'user' => '<img src="' . $user['headimgurl'] . '" class="img-thumbnail" style="padding:0;" width="30"/>&nbsp;' . $user['nickname'],
            'role' => $roles[$user['role_id']] ?? '客户',
            'name' => $user['name'],
            'tel' => $user['tel']
          ];
        }

        return $this->respondWithData(['users' => $datas, 'role' => $roles]);
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
