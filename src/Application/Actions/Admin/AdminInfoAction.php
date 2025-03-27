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
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\Setting;
use Wanphp\Libray\Slim\WpUserInterface;

class AdminInfoAction extends \App\Application\Actions\Action
{
  private AdminInterface $admin;
  private WpUserInterface $user;
  private Key $key;
  private string $basePath = '';

  /**
   * @param LoggerInterface $logger
   * @param Setting $setting
   * @param WpUserInterface $user
   * @param AdminInterface $admin
   * @throws BadFormatException
   * @throws EnvironmentIsBrokenException
   */
  public function __construct(
    LoggerInterface $logger,
    Setting         $setting,
    WpUserInterface $user,
    AdminInterface  $admin
  )
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->user = $user;
    $this->key = Key::loadFromAsciiSafeString($setting->get('oauth2Config')['encryptionKey']);
    $this->basePath = $setting->get('basePath');
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
          $msgData = [
            'template_id_short' => 'OPENTM407205168',//密码重置通知,所属行业编号21
            'url' => $this->httpHost() . $this->basePath . '/login',
            'data' => [
              'first' => array('value' => '您的账号刚刚修改了密码', 'color' => '#173177'),
              'keyword1' => array('value' => $this->admin->get('account', ['id' => $_SESSION['login_id']]), 'color' => '#173177'),
              'keyword2' => array('value' => $data['password'], 'color' => '#173177'),
              'remark' => array('value' => '如果不是您本人操作，则您的帐号存在安全风险，请及时联系管理员处理。', 'color' => '#173177')
            ]
          ];

          $this->user->sendMessage([$admin['uid']], $msgData);
        }
        return $this->respondWithData(['upNum' => $num], 201);
      } else {
        return $this->respondWithError('密码不能为空！！');
      }
    } else {
      $code = Crypto::encrypt(session_id(), $this->key);
      $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
      $writer = new Writer($renderer);
      $data = [
        'bindQr' => $writer->writeString($this->httpHost() . $this->basePath . '/admin/userBind?tk=' . $code),
        'unBindQr' => $writer->writeString($this->httpHost() . $this->basePath . '/admin/userUnBind?tk=' . $code)
      ];
      return $this->respondView('admin/admin/admininfo.html', $data);
    }

  }
}
