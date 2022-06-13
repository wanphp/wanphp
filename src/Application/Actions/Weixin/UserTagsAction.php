<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/10/9
 * Time: 14:43
 */

namespace App\Application\Actions\Weixin;


use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Weixin\WeChatBase;

/**
 * Class UserTagsAction
 * @title 粉丝标签
 * @route /admin/weixin/tags
 * @package App\Application\Actions\Weixin
 */
class UserTagsAction extends Action
{
  private WeChatBase $weChatBase;

  public function __construct(LoggerInterface $logger, WeChatBase $weChatBase)
  {
    parent::__construct($logger);
    $this->weChatBase = $weChatBase;
  }

  protected function action(): Response
  {
    if (!isset($_SESSION['wxuser_total'])) {
      $list = $this->weChatBase->getUserList();
      $_SESSION['wxuser_total'] = $list['total'];
    }

    $userTags = $this->weChatBase->getTags();
    $data = [
      'title' => '粉丝标签管理',
      'tags' => $userTags['tags'] ?? [],
      'total' => $_SESSION['wxuser_total']
    ];

    return $this->respondView('admin/weixin/tags.html', $data);
  }

}
