<?php

namespace App\Application\Actions\Weixin;

use App\Domain\Admin\AdminInterface;
use App\Domain\Grid\GridInterface;
use App\Domain\Weixin\UserInterface;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Weixin\Domain\PublicInterface;

/**
 * Class UserAction
 * @title 用户管理
 * @route /admin/weixin/users
 * @package App\Application\Actions\Weixin
 */
class UserAction extends \App\Application\Actions\Action
{
  private UserInterface $user;
  private PublicInterface $public;
  private AdminInterface $admin;

  public function __construct(UserInterface $user, PublicInterface $public, AdminInterface $admin)
  {
    $this->user = $user;
    $this->public = $public;
    $this->admin = $admin;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
      $where = [];
      $params = $this->request->getQueryParams();
      if (!empty($params['search']['value'])) {
        $keyword = trim($params['search']['value']);
        $where['OR'] = [
          'u.name[~]' => $keyword,
          'u.nickname[~]' => $keyword,
          'u.tel[~]' => $keyword
        ];
      }

      // 网格单位
      if (isset($params['pid']) && $params['pid'] > 0) {
        $where['p.parent_id'] = intval($params['pid']);
      }

      $recordsFiltered = $this->public->count('id', $where);
      $where['LIMIT'] = [$params['start'], $params['length']];
      $where['ORDER'] = ["u.id" => "DESC"];

      $data = [
        "draw" => $params['draw'],
        "recordsTotal" => $this->public->count('id'),
        "recordsFiltered" => $recordsFiltered,
        'data' => $this->user->getUsers($where)
      ];
      $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
      $this->response->getBody()->write($json);
      return $this->respond(200);
    } else {
      $data = [
        'title' => '用户管理'
      ];

      return $this->respondView('admin/user/list.html', $data);
    }
  }
}