<?php

namespace App\Application\Actions\Admin;

use App\Application\Handlers\UserHandler;
use App\Domain\Admin\AdminInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
        // 当前用户已绑定微信
        if ($_SESSION['user_id'] > 0) {
          // 重新绑定微信
          if ($admin['uid'] > 0 && $admin['uid'] != $_SESSION['user_id']) {
            $_SESSION['user_id'] = $admin['uid'];
            return $this->respondWithData(['res' => 'OK', 'message' => '重新绑定微信成功']);
          }
          // 解除微信绑定
          if ($admin['uid'] == 0) {
            $_SESSION['user_id'] = 0;
            return $this->respondWithData(['res' => 'OK', 'message' => '解除微信绑定成功']);
          }
        } else {
          // 绑定微信
          if (!empty($_SESSION['login_user_id']) && is_numeric($_SESSION['login_user_id'])) {
            $user_id = $_SESSION['login_user_id'];
            unset($_SESSION['login_user_id']);
            $_SESSION['user_id'] = $user_id;
            return $this->respondWithData(['res' => 'OK', 'message' => '微信绑定成功']);
          }
          return $this->respondWithError('尚未授权！');
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
          $admin = $this->admin->get('id,account', ['uid' => $user['id']]);
          if ($admin) {
            if ($admin['id'] == $_SESSION['login_id']) {
              $data = ['title' => '系统提醒',
                'msg' => '重复绑定，您应该使用新的微信扫码',
                'icon' => 'weui-icon-warn'
              ];
            } else {
              $data = ['title' => '系统提醒',
                'msg' => '您的微信已与”' . $admin['account'] . '“帐号绑定，需先解除才能绑定！！',
                'icon' => 'weui-icon-warn'
              ];
            }
            return $this->respondView('admin/error/wxerror.html', $data);
          }
          // 扫码微信未绑定过
          $data = ['uid' => $user['id'], 'name' => $user['name']];
          if ($user['tel']) $data['tel'] = $user['tel'];
          $up = $this->admin->update($data, ['id' => $_SESSION['login_id']]);
          if ($up > 0) {
            // 记录扫码微信uid，登录登录帐号uid为$_SESSION['user_id']，注意区分
            $_SESSION['login_user_id'] = $user['id'];
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
              'msg' => '您的帐号已成功与您的微信绑定！',
              'icon' => 'weui-icon-success'
            ];
            return $this->respondView('admin/error/wxerror.html', $data);
          } else {
            $data = ['title' => '系统提醒',
              'msg' => '绑定失败，请重试！！',
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

  /**
   * 解绑微信
   * @throws Exception
   */
  public function unBind(Request $request, Response $response, array $args): Response
  {
    $this->request = $request;
    $this->response = $response;
    $this->args = $args;

    $queryParams = $this->request->getQueryParams();
    if (isset($queryParams['code'])) {//微信公众号认证回调
      $user = UserHandler::getUser($this->request, $this->user);
      // 检查绑定管理员
      $admin_id = $this->getLoginId();
      $bindUid = $this->getLoginUserId();
      if ($user && $user['id'] > 0 && $admin_id > 0 && $bindUid == $user['id']) {
        $admin = $this->admin->get('account', ['uid' => $user['id']]);
        if ($admin) {
          $data = ['uid' => 0, 'name' => '', 'tel' => ''];
          $this->admin->update($data, ['id' => $_SESSION['login_id']]);
          // 清除当前用户session
          unset($_SESSION['login_user_id']);
          $data = ['title' => '解除绑定成功',
            'msg' => '您的微信已与”' . $admin . '“帐号成功解除绑定，可以绑定到其它账号！！',
            'icon' => 'weui-icon-success'
          ];
        } else {
          $data = ['title' => '系统提醒',
            'msg' => '重复解绑操作，您的微信当前未绑定此账号！！',
            'icon' => 'weui-icon-warn'
          ];
        }
      } else {
        $data = ['title' => '系统提醒',
          'msg' => '绑定帐号与当前授权用户不是一个用户！！',
          'icon' => 'weui-icon-warn'
        ];
      }
      return $this->respondView('admin/error/wxerror.html', $data);
    } else {
      return UserHandler::oauthRedirect($this->request, $this->response, $this->user);
    }
  }
}
