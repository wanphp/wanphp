<?php

namespace App\Application\Actions\Common;

use App\Application\Handlers\UserHandler;
use App\Domain\Admin\AdminInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class QrLoginAction extends \App\Application\Actions\Action
{
  private ContainerInterface $container;
  private AdminInterface $admin;

  public function __construct(LoggerInterface $logger, ContainerInterface $container, AdminInterface $admin)
  {
    parent::__construct($logger);
    $this->container = $container;
    $this->admin = $admin;
  }

  /**
   * @return Response
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   * @throws Exception
   */
  protected function action(): Response
  {
    if ($this->isPost()) {
      if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) return $this->respondWithData(['res' => 'OK']);
      else return $this->respondWithError('尚未授权！');
    } else {
      $queryParams = $this->request->getQueryParams();
      $state = $queryParams['state'] ?? '';
      if (isset($queryParams['code'])) {//微信公众号认证回调
        $user = UserHandler::getUser($this->request, $this->container);
        // 检查绑定管理员
        if ($user && $user['id'] > 0) {
          $admin = $this->admin->get('id,role_id[JSON],account,status', ['uid' => $user['id']]);
          if (!$admin) {
            $data = ['title' => '系统提醒',
              'msg' => '微信尚未绑定帐号，请使用密码登录！',
              'icon' => 'weui-icon-warn'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          }
          $params = $this->request->getServerParams();
          if ($admin['status'] == 1) {
            $_SESSION['login_id'] = $admin['id'];
            $_SESSION['role_id'] = $admin['role_id'];
            $_SESSION['user_id'] = $user['id'];
            $this->admin->update(['lastlogintime' => time(), 'lastloginip' => $params['REMOTE_ADDR']], ['id' => $admin['id']]);
            if ($state == 'weixin') {
              $this->logger->info('”' . $admin['account'] . '”刚刚通过微信内部授权登录了系统；绑定用户UID' . $user['id']);
              $backUrl = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/admin/index';
              return $this->response->withHeader('Location', $backUrl)->withStatus(301);
            } else {
              $data = ['title' => '登录成功',
                'msg' => '您已成功授权，详情查看PC端扫码页面！',
                'icon' => 'weui-icon-success'
              ];
              $this->logger->info('”' . $admin['account'] . '”刚刚通过微信扫码登录了系统；绑定用户UID' . $user['id']);
              return $this->respondView('admin/error/wxerror.html', $data);
            }
          } else {
            $data = ['title' => '系统提醒',
              'msg' => '帐号已被锁定，无法登录！！',
              'icon' => 'weui-icon-warn'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          }
        } else {
          return $this->respondWithError('未知用户！');
        }
      } else {
        return UserHandler::oauthRedirect($this->request, $this->response, $this->container);
      }
    }
  }
}