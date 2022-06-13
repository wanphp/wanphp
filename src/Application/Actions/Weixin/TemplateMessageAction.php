<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/4
 * Time: 10:55
 */

namespace App\Application\Actions\Weixin;


use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Weixin\WeChatBase;
use Wanphp\Plugins\Weixin\Domain\MsgTemplateInterface;

/**
 * Class TemplateMessageAction
 * @title 消息模板
 * @route /admin/weixin/template
 * @package App\Application\Actions\Weixin
 */
class TemplateMessageAction extends Action
{
  private MsgTemplateInterface $msgTemplate;
  private WeChatBase $weChatBase;

  public function __construct(LoggerInterface $logger, WeChatBase $weChatBase, MsgTemplateInterface $msgTemplate)
  {
    parent::__construct($logger);
    $this->msgTemplate = $msgTemplate;
    $this->weChatBase = $weChatBase;
  }

  protected function action(): Response
  {
    $data = [
      'title' => '消息模板管理',
      'industry' => $this->weChatBase->getIndustry()
    ];

    return $this->respondView('admin/weixin/template.html', $data);
  }
}
