<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/29
 * Time: 14:08
 */

namespace App\Application\Api\Weixin;


use App\Application\Api\Api;
use App\Domain\Weixin\PublicInterface;
use App\Domain\Weixin\UserInterface;
use App\Infrastructure\Weixin\WeChatBase;
use Psr\Http\Message\ResponseInterface as Response;

class WePublic extends Api
{
  private $weChatBase;
  private $user;
  private $public;

  public function __construct(WeChatBase $weChatBase, UserInterface $user, PublicInterface $public)
  {
    $this->weChatBase = $weChatBase;
    $this->user = $user;
    $this->public = $public;
  }

  protected function action(): Response
  {
    if ($this->weChatBase->valid() === true) {
      $openid = $this->weChatBase->getRev()->getRevFrom();//获取每个微信用户的openid
      $time = $this->weChatBase->getRev()->getRevCtime();//获取消息发送时间
      $msgid = $this->weChatBase->getRev()->getRevID();//获取消息ID
      $type = $this->weChatBase->getRev()->getRevType();//获取消息类型
      $text = $this->weChatBase->getRev()->getRevContent();//获取消息内容
      $eventarr = $this->weChatBase->getRev()->getRevEvent();//获取接收事件推送
      $event = $eventarr['event'] ?? '';//获得事件类型
      $eventkey = $eventarr['key'] ?? '';//获取Key值
      //不是事件消息，更新最后操作时间
      if ($type != 'event') $this->public->update(['lastop_time' => $time], ['openid' => $openid]);
      $body = '';
      switch ($type) {
        case 'event':
          if (in_array($event, array('CLICK', 'SCAN', 'scancode_push', 'scancode_waitmsg', 'merchant_order'))) {
            $this->public->update(['lastop_time' => $time], ['openid' => $openid]);
          }

          //if ($event == 'CLICK') {
          //关键词回复
          //$this->keywdreply($eventkey, $openid);
          //}
          //if ($event == "SCAN") {
          //  $this->qrlogin($openid, $eventkey);
          //}

          if ($event == 'subscribe') {//关注
            //保存用户信息
            $userinfo = $this->weChatBase->getUserInfo($openid);
            if (!is_array($userinfo)) $userinfo = [];
            if (isset($userinfo['groupid'])) unset($userinfo['groupid']);
            if (is_array($userinfo['tagid_list'])) $userinfo['tagid_list'] = join(',', $userinfo['tagid_list']);
            //本地存储用户
            $info = $this->public->get('*', ['openid' => $openid]);
            if (isset($info['id'])) {//二次关注
              //更新用户信息
              $this->user->update([
                'nickname' => $userinfo['nickname'],
                'headimgurl' => $userinfo['headimgurl'],
                'sex' => $userinfo['sex']
              ], ['id' => $info['id']]);
              //更新公众号信息
              $this->public->update([
                'subscribe' => 1,
                'subscribe_time' => $userinfo['subscribe_time'],
                'unsubscribe_time' => 0,
                'subscribe_scene' => $userinfo['subscribe_scene'],
                'lastop_time' => $time
              ], ['id' => $info['id']]);
            } else {
              //检查用户是否通过小程序等，存储到本地
              if (isset($userinfo['unionid'])) {
                $user_id = $this->user->get('id', ['unionid' => $userinfo['unionid']]);
                if ($user_id > 0) {
                  //更新用户信息
                  $this->user->update([
                    'nickname' => $userinfo['nickname'],
                    'headimgurl' => $userinfo['headimgurl'],
                    'sex' => $userinfo['sex']
                  ], ['id' => $info['id']]);
                } else {
                  $user_id = $this->user->insert([
                    'unionid' => $userinfo['unionid'],
                    'nickname' => $userinfo['nickname'],
                    'headimgurl' => $userinfo['headimgurl'],
                    'sex' => $userinfo['sex']
                  ]);
                }
              } else {
                $user_id = $this->user->insert([
                  'nickname' => $userinfo['nickname'],
                  'headimgurl' => $userinfo['headimgurl'],
                  'sex' => $userinfo['sex']
                ]);
              }
              //添加公众号信息
              $this->public->insert([
                'id' => $user_id,
                'openid' => $openid,
                'parent_id' => $userinfo['qr_scene'],
                'subscribe' => 1,
                'subscribe_time' => $userinfo['subscribe_time'],
                'subscribe_scene' => $userinfo['subscribe_scene'],
                'lastop_time' => $time
              ]);
            }
            //关注自动回复文本信息
            $body = $this->weChatBase->text('感谢关注！')->reply();
          }
          if ($event == 'unsubscribe') {
            $data = array();
            $data['subscribe'] = 0;
            $data['unsubscribe_time'] = $time;
            $data['integral'] = 0;
            $data['lastop_time'] = 0;
            $this->public->update($data, ['openid' => $openid]);
          }
          break;
        case 'text':
          //关键词回复

          break;
        case 'image':
          break;
        case 'voice':
          break;
        case 'video':
          break;
        case 'shortvideo':
          break;
        default:
          $body = $this->weChatBase->text('收到')->reply();
      }

    } else {
      $body = $this->weChatBase->valid();
    }
    $this->response->getBody()->write($body);

    return $this->response->withHeader('Content-Type', 'text/xml')->withStatus(200);
  }
}
