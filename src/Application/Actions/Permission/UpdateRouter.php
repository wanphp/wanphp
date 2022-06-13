<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/9
 * Time: 16:36
 */

namespace App\Application\Actions\Permission;


use Psr\Http\Message\ResponseInterface as Response;

class UpdateRouter extends Router
{
  protected function action(): Response
  {
    if ($this->isPost()) {
      $data = $this->request->getParsedBody();
      if (isset($data['id'])) {
        $where = ['id' => $data['id']];
        unset($data['id']);
        $num = $this->routerRepository->update($data, $where);
        return $this->respondWithData(['upNum' => $num]);
      } else {
        return $this->respondWithError('未知操作');
      }
    }
    return $this->respondWithError('非法请求');
  }

}
