<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/22
 * Time: 17:51
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use App\Domain\Admin\AdminInterface;
use App\Domain\Common\ClientsInterface;
use Psr\Http\Message\ResponseInterface as Response;

class InitSysApi extends Api
{
  private $admin;
  private $clients;

  public function __construct(AdminInterface $admin, ClientsInterface $clients)
  {
    $this->admin = $admin;
    $this->clients = $clients;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *  path="/auth/initSys",
   *  tags={"Auth"},
   *  summary="初始化系统",
   *  operationId="initSys",
   *  @OA\RequestBody(
   *     description="初始化数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(
   *         type="object",
   *         @OA\Property(
   *           property="account",
   *           type="string",
   *           example="root",
   *           description="系统管理帐号"
   *         ),
   *         @OA\Property(
   *           property="password",
   *           type="string",
   *           example="root",
   *           description="帐号密码"
   *         )
   *       )
   *     )
   *   ),
   *  @OA\Response(response="201",description="用户更新成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    $data = $this->request->getParsedBody();
    if (empty($data)) return $this->respondWithError('无用户数据');

    $uri = $this->request->getUri();
    try {
      $clients = $this->clients->select('id');
    } catch (\Exception $e) {
      $client_secret = md5(uniqid(rand(), true));
      if ($e->getCode() == '1146') {
        $this->clients->insert([
          'client_id' => 'sysmanage',
          'client_secret' => $client_secret,
          'name' => '系统管理',
          'redirect_uri' => $uri->getScheme() . '://' . $uri->getHost() . '/',
          'confidential' => 1
        ]);
      }
    }

    try {
      $admin = $this->admin->select('id');
    } catch (\Exception $e) {
      if ($e->getCode() == '1146') {
        $salt = substr(md5(uniqid(rand(), true)), 10, 11);
        $this->admin->insert([
          'account' => $data['account'],
          'salt' => $salt,
          'password' => md5(SHA1($salt . md5($data['password']))),
          'role_id' => -1,
          'status' => 1,
          'ctime' => time()
        ]);
      }
    }

    if (isset($clients) && isset($admin)) {
      return $this->respondWithError('系统已初始化过，请求被拒绝。');
    } else {
      $data = [
        'client_id' => 'sysmanage',
        'client_secret' => $client_secret,
        'account' => $data['account'],
        'password' => $data['password']
      ];
      return $this->respondWithData($data, 201);
    }
  }
}
