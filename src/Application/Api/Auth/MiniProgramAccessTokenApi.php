<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/10/15
 * Time: 14:27
 */

namespace App\Application\Api\Auth;


use Exception;
use Wanphp\Libray\Weixin\MiniProgram;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;
use Wanphp\Plugins\Weixin\Domain\MiniProgramInterface;

class MiniProgramAccessTokenApi extends Author2Api
{
  private $miniProgram;
  private $miniProgramUser;

  public function __construct(MiniProgramInterface $miniProgramUser, MiniProgram $miniProgram, ContainerInterface $container)
  {
    parent::__construct($container);
    $this->miniProgram = $miniProgram;
    $this->miniProgramUser = $miniProgramUser;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *   path="/auth/miniProgramAccessToken",
   *   tags={"Auth"},
   *   summary="小程序用户登录，获取访问令牌",
   *   operationId="miniProgramAccessToken",
   *   @OA\RequestBody(
   *     description="获取access_token",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(
   *         type="object",
   *         @OA\Property(
   *           property="grant_type",
   *           type="string",
   *           example="miniprogram",
   *           description="授权模式，值固定为：miniprogram"
   *         ),
   *         @OA\Property(
   *           property="client_id",
   *           type="string",
   *           description="客户端ID,由服务端分配"
   *         ),
   *         @OA\Property(
   *           property="client_secret",
   *           type="string",
   *           description="客户端 secret,由服务端分配"
   *         ),
   *         @OA\Property(
   *           property="user",
   *           description="调用wx.getUserInfo()获取的用户信息",
   *           type="object",
   *           @OA\Property(
   *            property="nickname",
   *            type="string",
   *            description="用户昵称，userInfo.nickName"
   *           ),
   *           @OA\Property(
   *            property="headimgurl",
   *            type="string",
   *            description="用户头像，userInfo.avatarUrl"
   *           ),
   *           @OA\Property(
   *            property="sex",
   *            type="integer",
   *            enum={0, 1, 2},
   *            description="用户性别（1男，2女，0保密），userInfo.gender"
   *           )
   *         ),
   *         @OA\Property(
   *           property="code",
   *           type="string",
   *           description="调用wx.login()接口获取登录凭证（code）。"
   *         )
   *       )
   *     )
   *   ),
   *   @OA\Response(
   *    response="201",
   *    description="获取AccessToken成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(
   *           property="datas",
   *           @OA\Property(property="token_type",type="string"),
   *           @OA\Property( property="expires_in",type="integer"),
   *           @OA\Property(property="access_token",type="string"),
   *           @OA\Property(property="refresh_token",type="string")
   *        )
   *       )
   *      }
   *    )
   *   ),
   *   @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    try {
      $this->miniProgram($this->miniProgram, $this->miniProgramUser);
      // 这里只需要这一行就可以，具体的判断在 Repositories 中
      return $this->server->respondToAccessTokenRequest($this->request, $this->response);
    } catch (OAuthServerException $exception) {
      return $exception->generateHttpResponse($this->response);
    } catch (Exception $exception) {
      $body = new Stream(fopen('php://temp', 'r+'));
      $body->write($exception->getMessage());
      return $this->response->withStatus(400)->withBody($body);
    }
  }

}
