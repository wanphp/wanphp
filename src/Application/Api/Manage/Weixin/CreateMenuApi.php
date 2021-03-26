<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2021/3/11
 * Time: 16:52
 */

namespace App\Application\Api\Manage\Weixin;


use App\Application\Api\Api;
use App\Domain\Weixin\CustomMenuInterface;
use App\Infrastructure\Weixin\WeChatBase;
use Psr\Http\Message\ResponseInterface as Response;

class CreateMenuApi extends Api
{
  private $weChatBase;
  private $customMenu;

  public function __construct(WeChatBase $weChatBase, CustomMenuInterface $customMenu)
  {
    $this->weChatBase = $weChatBase;
    $this->customMenu = $customMenu;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *  path="/api/manage/weixin/createMenu",
   *  tags={"WeixinCustomMenu"},
   *  summary="创建公众号自定义菜单",
   *  operationId="createWeixinCustomMenu",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="创建自定义菜单，tag_id=0为默认菜单",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",@OA\Schema(@OA\Property(property="tag_id",type="number",description="粉丝标签"))
   *     )
   *   ),
   *  @OA\Response(response="201",description="添加成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    $data = $this->request->getParsedBody();
    $tag_id = $data['tag_id'] ?? 0;
    $where = ['tag_id' => $tag_id, 'parent_id' => 0, 'ORDER' => ['tag_id' => 'ASC', 'parent_id' => 'ASC', 'sort_order' => 'ASC']];
    $menus = [];
    foreach ($this->customMenu->select('*', $where) as $item) {
      $menu = ['name' => $item['name']];
      $where['parent_id'] = $item['id'];
      $subMenus = $this->customMenu->select('*', $where);
      if (count($subMenus) > 0) {
        foreach ($subMenus as $btn) {
          $subBtn = ['name' => $btn['name'], 'type' => $btn['type']];
          switch ($btn['type']) {
            case 'view':
              $subBtn['url'] = $btn['url'];
              break;
            case 'miniprogram':
              $subBtn['url'] = $btn['url'];
              $subBtn['appid'] = $btn['appid'];
              $subBtn['pagepath'] = $btn['pagepath'];
              break;
            default:
              $subBtn['key'] = $btn['key'];
          }
          $menu['sub_button'][] = $subBtn;
        }
      } else {
        $menu['type'] = $item['type'];
        switch ($item['type']) {
          case 'view':
            $menu['url'] = $item['url'];
            break;
          case 'miniprogram':
            $menu['url'] = $item['url'];
            $menu['appid'] = $item['appid'];
            $menu['pagepath'] = $item['pagepath'];
            break;
          default:
            $menu['key'] = $item['key'];
        }
      }
      $menus[] = $menu;
    }
    if ($menus) {
      if ($tag_id == 0) {
        $result = $this->weChatBase->createMenu(['button' => $menus]);
      } else {
        $result = $this->weChatBase->addconditional(['button' => $menus, 'matchrule' => ['tag_id' => $tag_id]]);
      }
      return $this->respondWithData($result);
    } else {
      return $this->respondWithError('菜单为空！', 400);
    }
  }
}