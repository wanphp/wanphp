<?php

namespace App\Application\Actions\Admin;

use App\Domain\Admin\AdminInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Weixin\WeChatBase;
use Wanphp\Plugins\Weixin\Domain\PublicInterface;

class AdminInfoAction extends \App\Application\Actions\Action
{
  private AdminInterface $admin;
  private WeChatBase $weChatBase;
  private PublicInterface $public;
  private Key $key;

  /**
   * @param LoggerInterface $logger
   * @param ContainerInterface $container
   * @param AdminInterface $admin
   * @param WeChatBase $weChatBase
   * @param PublicInterface $public
   * @throws BadFormatException
   * @throws ContainerExceptionInterface
   * @throws EnvironmentIsBrokenException
   * @throws NotFoundExceptionInterface
   */
  public function __construct(
    LoggerInterface    $logger,
    ContainerInterface $container,
    AdminInterface     $admin,
    WeChatBase         $weChatBase,
    PublicInterface    $public
  )
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->weChatBase = $weChatBase;
    $this->public = $public;
    $this->key = Key::loadFromAsciiSafeString($container->get('settings')['encryptionKey']);
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->isPost()) {
      $data = $this->request->getParsedBody();
      if (!empty($data['password'])) {
        $salt = substr(md5(uniqid(rand(), true)), 10, 11);
        $password = [
          'salt' => $salt,
          'password' => md5(SHA1($salt . md5($data['password'])))
        ];
        $num = $this->admin->update($password, ['id' => $_SESSION['login_id']]);
        $admin = $this->admin->get('account,uid', ['id' => $_SESSION['login_id']]);
        if ($admin['uid'] > 0) {
          // 发公众号通知
          $msgdata = array('touser' => $this->public->get('openid', ['id' => $admin['uid']]),
            'template_id' => '', //TODO 自行绑定公众号模板消息ID
            'url' => $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/login',
            'data' => array(
              'first' => array('value' => '您的账号刚刚修改了密码', 'color' => '#173177'),
              'keyword1' => array('value' => $this->admin->get('account', ['id' => $_SESSION['login_id']]), 'color' => '#173177'),
              'keyword2' => array('value' => $data['password'], 'color' => '#173177'),
              'remark' => array('value' => '如果不是您本人操作，则您的帐号存在安全风险，请及时联系管理员处理。', 'color' => '#173177')
            ));
          $this->weChatBase->sendTemplateMessage($msgdata);
        }
        return $this->respondWithData(['upNum' => $num], 201);
      } else {
        return $this->respondWithError('密码不能为空！！');
      }
    } else {
      $code = Crypto::encrypt(session_id(), $this->key);
      $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
      $writer = new Writer($renderer);
      $data = ['bindQr' => $writer->writeString($this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/admin/userbind?tk=' . $code)];
      return $this->respondView('admin/admin/admininfo.html', $data);
    }

  }
}