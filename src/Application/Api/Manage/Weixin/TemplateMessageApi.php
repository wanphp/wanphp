<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/29
 * Time: 14:59
 */

namespace App\Application\Api\Manage\Weixin;


use App\Application\Api\Api;
use App\Domain\Weixin\MsgTemplateInterface;
use App\Infrastructure\Weixin\WeChatBase;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class TemplateMessageApi
 * @title 消息模板
 * @route /api/manage/weixin/tplmsg
 * @package App\Application\Api\Manage\Weixin
 */
class TemplateMessageApi extends Api
{
  private $weChatBase;
  private $msgTemplate;

  public function __construct(WeChatBase $weChatBase, MsgTemplateInterface $msgTemplate)
  {
    $this->weChatBase = $weChatBase;
    $this->msgTemplate = $msgTemplate;
  }

  /**
   * @return Response
   * @throws \Exception
   * @OA\Post(
   *  path="/api/manage/weixin/tplmsg",
   *  tags={"TemplateMessage"},
   *  summary="添加消息模板",
   *  operationId="addTemplateMessage",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(type="object",@OA\Property(property="tplid",description="模板ID",type="string"))
   *     )
   *   ),
   *  @OA\Response(response="201",description="添加成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Delete(
   *  path="/api/manage/weixin/tplmsg/{tplID}",
   *  tags={"TemplateMessage"},
   *  summary="删除消息模板",
   *  operationId="delTemplateMessage",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="ID",
   *    in="path",
   *    description="模板ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="string")
   *  ),
   *  @OA\Response(response="204",description="删除成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/manage/weixin/tplmsg",
   *  tags={"TemplateMessage"},
   *  summary="消息模板",
   *  operationId="listTemplateMessage",
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
        $result = $this->weChatBase->addTemplateMessage($data['tplid']);
        if ($result && $result['errcode'] == 0) {
          $data = array(
            'template_id_short' => $data['tmpid'],
            'template_id' => $result['template_id'],
            'status' => 1
          );
          $id = $this->msgTemplate->insert($data);
          return $this->respondWithData(['id' => $id], 201);
        } else {
          return $this->respondWithError($result['errmsg']);
        }
        break;
      case 'DELETE':
        $template_id = $this->args['tplid'] ?? '';
        if ($template_id) {
          $result = $this->weChatBase->delTemplateMessage($template_id);
          if ($result && $result['errcode'] == 0) {
            $this->msgTemplate->delete(['template_id' => $template_id]);
            return $this->respondWithData($result, 204);
          } else {
            return $this->respondWithError($result['errmsg']);
          }
        } else {
          return $this->respondWithError('缺少模板ID');
        }
        break;
      case 'GET':
        $msgtemplate = $this->weChatBase->templateMessage();
        $list = $this->msgTemplate->select('*');
        $templates = [];
        if (is_array($msgtemplate['template_list'])) foreach ($msgtemplate['template_list'] as $vo) {
          $templates[$vo['template_id']] = $vo;
        }

        foreach ($list as &$item) {
          if (isset($templates[$item['template_id']])) {
            $item = array_merge($item, $templates[$item['template_id']]);
            unset($templates[$item['template_id']]);
          } else {
            $this->msgTemplate->update(['status' => 0], ['id' => $item['id']]);
            $item['template_id'] = '公众平台后台删除';
            $item['status'] = 0;
          }
        }

        if (count($templates) > 0) foreach ($templates as $template) {
          $template['status'] = 1;
          $template['content'] = nl2br($template['content']);
          $list[] = $template;
        }

        $datas = [
          'industry' => $this->weChatBase->getIndustry(),
          'msg_templates' => $list ?? [],
        ];

        return $this->respondWithData($datas);
        break;
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
