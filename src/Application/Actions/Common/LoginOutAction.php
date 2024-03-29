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
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\Setting;

class LoginOutAction extends Action
{
  private string $basePath = '';

  public function __construct(LoggerInterface $logger, Setting $setting)
  {
    parent::__construct($logger);
    $this->basePath = $setting->get('basePath');
  }

  protected function action(): Response
  {
    $params = $this->request->getServerParams();
    $this->logger->log(0, '退出系统，IP：' . $this->getIP() . '，客户端：' . $params['HTTP_USER_AGENT']);
    $this->logger->info("退出系统，用户ID为：`{$_SESSION['login_id']}`。");
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    return $this->response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
  }

}
