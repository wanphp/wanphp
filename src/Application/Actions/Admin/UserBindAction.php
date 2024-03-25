<?php

namespace App\Application\Actions\Admin;

use App\Application\Handlers\UserHandler;
use App\Domain\Admin\AdminInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\WpUserInterface;

class UserBindAction extends \App\Application\Actions\Action
{

  private WpUserInterface $user;
  private AdminInterface $admin;

  public function __construct(
    LoggerInterface $logger,
    WpUserInterface $user,
    AdminInterface  $admin
  )
  {
    parent::__construct($logger);
    $this->user = $user;
    $this->admin = $admin;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->isPost()) {
      if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) {
        $admin = $this->admin->get('uid,account', ['id' => $_SESSION['login_id']]);
        if (isset($admin['uid']) && $admin['uid'] > 0 && $admin['uid'] != $_SESSION['user_id']) {
          // 记录绑定用户ID
          $_SESSION['user_id'] = $admin['uid'];
          return $this->respondWithData(['res' => 'OK']);
        } else {
          return $this->respondWithError('尚未绑定！');
        }
      } else {
        return $this->respondWithError('未知用户！');
      }
    } else {
      $queryParams = $this->request->getQueryParams();
      if (isset($queryParams['code'])) {//微信公众号认证回调
        $user = UserHandler::getUser($this->request, $this->user);
        // 检查绑定管理员
        if ($user && $user['id'] > 0 && isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) {
          $admin = $this->admin->get('account', ['uid' => $user['id']]);
          if ($admin) {
            $data = ['title' => '系统提醒',
              'msg' => '您的微信已与”' . $admin . '“帐号绑定，需先解除才能绑定！！',
              'icon' => 'weui-icon-warn'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          }
          $data = ['uid' => $user['id'], 'name' => $user['name']];
          if ($user['tel']) $data['tel'] = $user['tel'];
          $up = $this->admin->update($data, ['id' => $_SESSION['login_id']]);
          if ($up > 0) {
            $account = $this->admin->get('account', ['id' => $_SESSION['login_id']]);
            $msgData = [
              'template_id_short' => 'OPENTM405636750',//绑定状态通知,所属行业编号21
              'data' => [
                'keyword1' => ['value' => $user['name'] ?: '未完善', 'color' => '#173177'],
                'keyword2' => ['value' => '绑定帐号“' . $account . '”成功', 'color' => '#173177'],
                'keyword3' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177']
              ]
            ];
            $this->user->sendMessage([$user['id']], $msgData);
            // 通知解绑用户
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
              $unBindUser = $this->user->getUser($_SESSION['user_id']);
              $msgData = [
                'template_id_short' => 'OPENTM405636750',//绑定状态通知,所属行业编号21
                'data' => [
                  'keyword1' => ['value' => $unBindUser['name'] ?: '未完善', 'color' => '#173177'],
                  'keyword2' => ['value' => '解除绑定帐号“' . $account . '”成功', 'color' => '#173177'],
                  'keyword3' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177']
                ]];
              $this->user->sendMessage([$_SESSION['user_id']], $msgData);
            }

            $data = ['title' => '绑定成功',
              'msg' => '您的帐号已成功已您的微信绑定！',
              'icon' => 'weui-icon-success'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          } else {
            $data = ['title' => '系统提醒',
              'msg' => '重复绑定操作，您应该使用新的微信扫码！！',
              'icon' => 'weui-icon-warn'
            ];
          }
        } else {
          $data = ['title' => '系统提醒',
            'msg' => '未知用户，帐号绑定失败！！',
            'icon' => 'weui-icon-warn'
          ];
        }
        return $this->respondView('admin/error/wxerror.html', $data);
      } else {
        return UserHandler::oauthRedirect($this->request, $this->response, $this->user);
      }
    }
  }
}
