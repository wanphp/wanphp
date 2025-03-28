<?php

namespace App\Application\Actions\Common;

use App\Application\Handlers\UserHandler;
use App\Domain\Admin\AdminInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\Setting;
use Wanphp\Libray\Slim\WpUserInterface;

class QrLoginAction extends \App\Application\Actions\Action
{
  private WpUserInterface $user;
  private AdminInterface $admin;
  private string $basePath = '';

  /**
   * @throws Exception
   */
  public function __construct(LoggerInterface $logger, WpUserInterface $user, AdminInterface $admin, Setting $setting)
  {
    parent::__construct($logger);
    $this->user = $user;
    $this->admin = $admin;
    $this->basePath = $setting->get('basePath');
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    if ($this->isPost()) {
      if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) {
        $device = $this->request->getHeaderLine('X-HTTP-Device');
        $this->logger->log(0, '通过微信认证登录系统，登录IP：' . $this->getIP() . '，客户端：' . $device . '；授权用户UID' . $_SESSION['user_id']);
        return $this->respondWithData(['res' => 'OK']);
      } else if (isset($_SESSION['login_user_id']) && is_numeric($_SESSION['login_user_id'])) {
        // 没有网页授权获取用户基本信息，通过公众号被动回复连接授权登录
        $user_id = $_SESSION['login_user_id'];
        unset($_SESSION['login_user_id']);
        $admin = $this->admin->get('id,role_id,groupId,account,status', ['uid' => $user_id]);
        if (!$admin) {
          return $this->respondWithData(['res' => 'OK', 'errMsg' => '微信尚未绑定帐号，请使用密码登录！']);
        }
        if ($admin['status'] == 1) {
          $_SESSION['login_id'] = $admin['id'];
          $_SESSION['role_id'] = $admin['role_id'];
          $_SESSION['groupId'] = $admin['groupId'];
          $_SESSION['user_id'] = $user_id;
          $this->admin->update(['lastLoginTime' => time(), 'lastLoginIp' => $this->getIP()], ['id' => $admin['id']]);
          return $this->respondWithData(['res' => 'OK']);
        } else {
          return $this->respondWithData(['res' => 'OK', 'errMsg' => '帐号已被锁定，无法登录！！']);
        }
      } else return $this->respondWithError('尚未授权！');
    } else {
      $queryParams = $this->request->getQueryParams();
      $state = $queryParams['state'] ?? '';
      if (isset($queryParams['code'])) {//微信公众号认证回调
        $user = UserHandler::getUser($this->request, $this->user);
        // 检查绑定管理员
        if (isset($user['id']) && $user['id'] > 0) {
          $admin = $this->admin->get('id,role_id,groupId,account,status', ['uid' => $user['id']]);
          if (!$admin) {
            $data = ['title' => '系统提醒',
              'msg' => '微信尚未绑定帐号，请使用密码登录！',
              'icon' => 'weui-icon-warn'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          }
          if ($admin['status'] == 1) {
            $_SESSION['login_id'] = $admin['id'];
            $_SESSION['role_id'] = $admin['role_id'];
            $_SESSION['groupId'] = $admin['groupId'];
            $_SESSION['user_id'] = $user['id'];
            $this->admin->update(['lastLoginTime' => time(), 'lastLoginIp' => $this->getIP()], ['id' => $admin['id']]);
            if ($state == 'weixin') {
              $this->logger->log(0, '通过微信内部授权登录系统，登录IP：' . $this->getIP() . '，授权用户UID' . $_SESSION['user_id']);
              $this->logger->info('”' . $admin['account'] . '”刚刚通过微信内部授权登录了系统；绑定用户UID' . $user['id']);
              $backUrl = $_SESSION['redirect_uri'] ?? $this->httpHost() . $this->basePath . '/';
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
        // 记录当前URI
        $redirect_uri = $this->request->getHeaderLine('Referer');
        if (isset($queryParams['fragment'])) $redirect_uri .= '#' . $queryParams['fragment'];
        if (str_contains($redirect_uri, '/login')) $redirect_uri = $this->httpHost() . $this->basePath . '/#/admin/dashboard';
        $_SESSION['redirect_uri'] = $redirect_uri;
        return UserHandler::oauthRedirect($this->request, $this->response, $this->user);
      }
    }
  }
}
