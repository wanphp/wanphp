<?php

namespace App\Application\Actions\Common;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\WpUserInterface;

class SearchUserAction extends \App\Application\Actions\Action
{
  private WpUserInterface $user;

  public function __construct(WpUserInterface $user)
  {
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $params = $this->request->getQueryParams();
    if (isset($params['q']) && $params['q'] != '') {
      $keyword = trim($params['q']);
    } else {
      return $this->respondWithError('关键词不能为空！');
    }
    return $this->respondWithData($this->user->searchUsers($keyword, intval($params['page'] ?? 1)));
  }
}
