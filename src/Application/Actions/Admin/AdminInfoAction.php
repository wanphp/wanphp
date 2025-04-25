<?php

namespace App\Application\Actions\Admin;

use App\Application\Common\Message\MessageInterface;
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

class AdminInfoAction extends \App\Application\Actions\Action
{
  private AdminInterface $admin;
  private MessageInterface $message;
  private Key $key;
  private string $basePath;
  private string $scope;

  /**
   * @param LoggerInterface $logger
   * @param Setting $setting
   * @param MessageInterface $message
   * @param AdminInterface $admin
   * @throws BadFormatException
   * @throws EnvironmentIsBrokenException
   */
  public function __construct(
    LoggerInterface  $logger,
    Setting          $setting,
    MessageInterface $message,
    AdminInterface   $admin
  )
  {
    parent::__construct($logger);
    $this->admin = $admin;
    $this->message = $message;
    $this->key = Key::loadFromAsciiSafeString($setting->get('oauth2Config')['encryptionKey']);
    $this->basePath = $setting->get('basePath');
    $this->scope = $setting->get('oauth2Config')['scope'] ?? '';
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
          $this->message->editPassword([
            'account' => $this->admin->get('account', ['id' => $_SESSION['login_id']]),
            'password' => $data['password'],
            'url' => $this->httpHost() . $this->basePath . '/#/admin/dashboard?tk=' . Crypto::encrypt(session_id(), $this->key)
          ])->send([$admin['uid']]);
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
        'bindQr' => $writer->writeString($this->httpHost() . $this->basePath . '/admin/userBind?scope=' . $this->scope . '&tk=' . $code),
        'unBindQr' => $writer->writeString($this->httpHost() . $this->basePath . '/admin/userUnBind?scope=' . $this->scope . '&tk=' . $code)
      ];
      return $this->respondView('admin/admin/admininfo.html', $data);
    }

  }
}
