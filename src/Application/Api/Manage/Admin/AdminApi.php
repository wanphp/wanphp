<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 14:58
 */

namespace App\Application\Api\Manage\Admin;


use App\Application\Api\Api;
use App\Domain\Admin\AdminInterface;
use App\Domain\Admin\RoleInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class AdminApi
 * @title 管理员管理
 * @route /api/manage/admin
 * @package App\Application\Api\Manage
 */
class AdminApi extends Api
{
  private AdminInterface $admin;
  private RoleInterface $role;

  public function __construct(AdminInterface $admin, RoleInterface $role)
  {
    $this->admin = $admin;
    $this->role = $role;
  }

  /**
   * @return Response
   * @throws Exception
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
   *         @OA\Property(property="id",type="integer")
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
   *         @OA\Property(property="upNum",type="integer")
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
   *    response="200",
   *    description="删除成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="deNum",type="integer")
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
   *         @OA\Property(property="datas",type="array",@OA\Items(ref="#/components/schemas/AdminEntity"))
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
        $id = $this->admin->get('id', ['account' => $data['account']]);
        if (is_numeric($id) && $id > 0) {
          return $this->respondWithError('帐号已经存在');
        }
        $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
        $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        $data['ctime'] = time();
        $data['id'] = $this->admin->insert($data);
        return $this->respondWithData($data, 201);
      case  'PUT';
        $id = (int)$this->resolveArg('id');
        $data = $this->request->getParsedBody();
        $admin_id = $this->admin->get('id', ['id[!]' => $id, 'account' => $data['account']]);
        if (is_numeric($admin_id) && $admin_id > 0) {
          return $this->respondWithError('帐号已经存在');
        }
        if (isset($data['password'])) {
          $data['salt'] = substr(md5(uniqid(rand(), true)), 10, 11);
          $data['password'] = md5(SHA1($data['salt'] . md5($data['password'])));
        }
        $num = $this->admin->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->admin->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum], 200);
      case 'GET';
        $role_id = $this->request->getAttribute('oauth_admin_role_id');
        $roles = $this->role->select('id,name');
        $data = [];
        foreach ($this->admin->select('id,account,uid,role_id,name,tel,status,lastlogintime,lastloginip', ['role_id[>]' => $role_id]) as $admin) {
          $admin['lastlogintime'] = $admin['lastlogintime'] ? date('Y-m-d H:i:s', $admin['lastlogintime']) : '尚未登录';
          $data[] = $admin;
        }
        return $this->respondWithData(['admins' => $data, 'roles' => $roles]);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
