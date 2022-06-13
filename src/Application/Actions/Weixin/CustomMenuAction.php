<?php

namespace App\Application\Actions\Weixin;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Wanphp\Libray\Weixin\WeChatBase;
use Wanphp\Plugins\Weixin\Domain\CustomMenuInterface;

/**
 * Class CustomMenuAction
 * @title 自定义菜单
 * @route /admin/weixin/custom_menu
 * @package App\Application\Actions\Weixin
 */
class CustomMenuAction extends \App\Application\Actions\Action
{
  private WeChatBase $weChatBase;
  private CustomMenuInterface $customMenu;

  public function __construct(LoggerInterface $logger, WeChatBase $weChatBase, CustomMenuInterface $customMenu)
  {
    parent::__construct($logger);
    $this->weChatBase = $weChatBase;
    $this->customMenu = $customMenu;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $userTags = $this->weChatBase->getTags();
    $data = [
      'tags' => $userTags['tags']
    ];
    $data['tag_id'] = intval($this->args['id'] ?? 0);
    $where = ['tag_id' => $data['tag_id'], 'parent_id' => 0, 'ORDER' => ['tag_id' => 'ASC', 'parent_id' => 'ASC', 'sortOrder' => 'ASC']];
    $menus = [];
    foreach ($this->customMenu->select('*', $where) as $item) {
      $where['parent_id'] = $item['id'];
      $item['subBtn'] = $this->customMenu->select('*', $where);
      $menus[] = $item;
    }
    $tags = array_column($data['tags'], 'name', 'id');
    $data['tagTitle'] = $tags[$data['tag_id']] ?? '默认';
    $data['menus'] = $menus;
    $data['menuTitle'] = "添加{$data['tagTitle']}一级菜单";

    return $this->respondView('admin/weixin/custom-menu.html', $data);
  }
}