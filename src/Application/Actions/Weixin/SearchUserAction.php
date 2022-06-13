<?php

namespace App\Application\Actions\Weixin;

use App\Domain\Weixin\UserInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class SearchUserAction extends \App\Application\Actions\Action
{
  private UserInterface $user;

  public function __construct(LoggerInterface $logger, UserInterface $user)
  {
    parent::__construct($logger);
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $where = [];
    $params = $this->request->getQueryParams();
    if (isset($params['q']) && $params['q'] != '') {
      $keyword = trim($params['q']);
      $where['OR'] = [
        'name[~]' => $keyword,
        'nickname[~]' => $keyword,
        'tel[~]' => $keyword
      ];
    }
    $page = (intval($params['page'] ?? 0) - 1) * 10;
    $where['LIMIT'] = [$page, 10];

    $data = [
      'users' => $this->user->select('id,nickname,headimgurl,name,tel', $where),
      'total' => $this->user->count('id', $where)
    ];
    return $this->respondWithData($data);
  }
}