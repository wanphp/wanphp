<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/10
 * Time: 16:43
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class LoginOutAction extends Action
{
  protected function action(): Response
  {
    $this->logger->info("退出系统，用户ID为：`{$_SESSION['login_id']}`。");
    session_unset();
    session_destroy();
    return $this->response->withHeader('Location', '/login')->withStatus(302);
  }

}
