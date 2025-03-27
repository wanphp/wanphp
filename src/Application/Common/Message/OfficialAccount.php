<?php

namespace App\Application\Common\Message;

use Wanphp\Libray\Slim\WpUserInterface;

class OfficialAccount implements MessageInterface
{
  private WpUserInterface $user;
  private array $msgData;

  public function __construct(WpUserInterface $user)
  {
    $this->user = $user;
  }

  public function send(array $sendUser): array
  {
    return $this->user->sendMessage($sendUser, $this->msgData);
  }

  /**
   * @inheritDoc
   */
  public function userBind(array $data): MessageInterface
  {
    // TODO 构建通知数据包，这里使用测试公众号
    $this->msgData = [
      'template_id' => 'ctUQ7RzvGRRuO3D_QJgKZ7TSgo-cevjj2xuMxWszJes',
      'data' => [
        'keyword1' => ['value' => "帐号{$data['account']}绑定成功"],
        'keyword2' => ['value' => date('Y-m-d H:i')],
      ]
    ];
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function userUnBind(array $data): MessageInterface
  {
    // TODO 构建通知数据包，这里使用测试公众号
    $this->msgData = [
      'template_id' => 'ctUQ7RzvGRRuO3D_QJgKZ7TSgo-cevjj2xuMxWszJes',
      'data' => [
        'keyword1' => ['value' => "帐号{$data['account']}解绑成功"],
        'keyword2' => ['value' => date('Y-m-d H:i')],
      ]
    ];
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function login(array $data): MessageInterface
  {
    // TODO 构建通知数据包，这里使用测试公众号
    $this->msgData = [
      'template_id' => 'EHc3QufP4_ZO0NPkU1z7iZKkoWweaiGPQRVorlsO4Is',
      'url' => $data['url'],
      'data' => [
        'keyword1' => ['value' => $data['account'] . '，通过密码登录了系统'],
        'keyword2' => ['value' => date('Y-m-d H:i')],
      ]
    ];
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function editPassword(array $data): MessageInterface
  {
    // TODO 构建通知数据包，这里使用测试公众号
    $this->msgData = [
      'template_id' => 'yDsZoe5nIbwPuxlYETVLakaCjsreqzKKJ4Rl-bTA6Zo',
      'data' => [
        'keyword1' => ['value' => $data['account']],
        'keyword2' => ['value' => $data['password']],
      ]
    ];
    return $this;
  }
}