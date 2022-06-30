<?php

namespace App\Application\Actions\Admin;

use App\Domain\Admin\AdminInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Weixin\WeChatBase;
use Wanphp\Plugins\OAuth2Authorization\Application\WePublicUserHandler;
use Wanphp\Plugins\Weixin\Domain\PublicInterface;
use Wanphp\Plugins\Weixin\Domain\UserInterface;

class UserBindAction extends \App\Application\Actions\Action
{

  private WeChatBase $weChatBase;
  private PublicInterface $public;
  private UserInterface $user;
  private AdminInterface $admin;

  public function __construct(LoggerInterface $logger, PublicInterface $public, UserInterface $user, WeChatBase $weChatBase, AdminInterface $admin)
  {
    parent::__construct($logger);
    $this->public = $public;
    $this->user = $user;
    $this->weChatBase = $weChatBase;
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
        $user_id = WePublicUserHandler::getUserId($this->public, $this->user, $this->weChatBase);
        // 检查绑定管理员
        if ($user_id > 0 && isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])) {
          $admin = $this->admin->get('account', ['uid' => $user_id]);
          if ($admin) {
            $data = ['title' => '系统提醒',
              'msg' => '您的微信已与”' . $admin . '“帐号绑定，需先解除才能绑定！！',
              'icon' => 'weui-icon-warn'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          }
          $user = $this->user->get('name,tel', ['id' => $user_id]);
          $openid = $this->public->get('openid', ['id' => $user_id]);
          $up = $this->admin->update(['uid' => $user_id, 'name' => $user['name'], 'tel' => $user['tel']], ['id' => $_SESSION['login_id']]);
          if ($up > 0) {
            // 发公众号通知
            $account = $this->admin->get('account', ['id' => $_SESSION['login_id']]);
            $data = array('touser' => $openid,
              'template_id' => '', //TODO 自行绑定公众号模板消息ID
              'data' => array(
                'first' => array('value' => '您的微信已绑定管理帐号“' . $account . '”。', 'color' => '#173177'),
                'keyword1' => array('value' => $user['name'] ?: '未完善', 'color' => '#173177'),
                'keyword2' => array('value' => '绑定成功', 'color' => '#173177'),
                'keyword3' => array('value' => date('Y-m-d H:i:s'), 'color' => '#173177'),
                'remark' => array('value' => '以后可以使用微信扫码登录系统。', 'color' => '#173177')
              )
            );
            $this->weChatBase->sendTemplateMessage($data);
            if ($_SESSION['user_id'] > 0) {
              $openid = $this->public->get('openid', ['id' => $_SESSION['user_id']]);
              $user = $this->user->get('name,tel', ['id' => $_SESSION['user_id']]);
              $data = array('touser' => $openid,
                'template_id' => '', //TODO 自行绑定公众号模板消息ID
                'data' => array(
                  'first' => array('value' => '您的微信已解除与管理帐号“' . $account . '”的绑定。', 'color' => '#173177'),
                  'keyword1' => array('value' => $user['name'] ?: '未完善', 'color' => '#173177'),
                  'keyword2' => array('value' => '解除绑定成功', 'color' => '#173177'),
                  'keyword3' => array('value' => date('Y-m-d H:i:s'), 'color' => '#173177'),
                  'remark' => array('value' => '解除绑定后，不能再使用微信扫码登录系统。', 'color' => '#173177')
                ));
              $this->weChatBase->sendTemplateMessage($data);
              $_SESSION['user_id'] = $user_id;
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
            return $this->respondView('admin/error/wxerror.html', $data);
          }
        } else {
          $data = ['title' => '系统提醒',
            'msg' => '未知用户，帐号绑定失败！！',
            'icon' => 'weui-icon-warn'
          ];
          return $this->respondView('admin/error/wxerror.html', $data);
        }
      } else {
        return WePublicUserHandler::publicOauthRedirect($this->request, $this->response, $this->weChatBase);
      }
    }
  }
}