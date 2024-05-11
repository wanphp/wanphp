<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/3
 * Time: 16:52
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
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

class LoginAction extends Action
{
  private AdminInterface $adminRepository;
  private WpUserInterface $user;
  private Key $key;
  private string $basePath = '';
  private string $systemName = '';

  /**
   * @param LoggerInterface $logger
   * @param Setting $setting
   * @param AdminInterface $adminRepository
   * @param WpUserInterface $user
   * @throws BadFormatException
   * @throws EnvironmentIsBrokenException
   */
  public function __construct(
    LoggerInterface $logger,
    Setting         $setting,
    AdminInterface  $adminRepository,
    WpUserInterface $user
  )
  {
    parent::__construct($logger);
    $this->adminRepository = $adminRepository;
    $this->user = $user;
    $this->key = Key::loadFromAsciiSafeString($setting->get('oauth2Config')['encryptionKey']);
    $this->basePath = $setting->get('basePath');
    $this->systemName = $setting->get('systemName');
  }

  protected function action(): Response
  {
    if ($this->isPost()) {
      //获取数据
      $post = $this->request->getParsedBody();
      $account = trim($post['account'] ?? '');
      $password = md5(trim($post['password'] ?? ''));

      if (empty($account)) return $this->respondWithError('请输入用户名！');
      if (empty($password)) return $this->respondWithError('请输入密码！');

      $admin = $this->adminRepository->get('id,uid,account');

      if (!isset($admin['id'])) {//没有添加过管理员
        $salt = substr(md5(uniqid(rand(), true)), 10, 11);
        $id = $this->adminRepository->insert([
          'account' => $account,
          'salt' => $salt,
          'password' => md5(SHA1($salt . $password)),
          'role_id' => -1,
          'status' => 1,
          'createtime' => time()
        ]);
        $_SESSION['login_id'] = $id;
        $_SESSION['role_id'] = -1;
        return $this->respondWithData(['msg' => '系统初始化并登录成功！', 'redirect_uri' => $this->httpHost() . $this->basePath . '/admin/dashboard']);
      } else {
        $admin = $this->adminRepository->get('id,uid,account,salt,password,role_id,groupId,status', ['OR' => ['account' => $account, 'tel' => $account]]);
      }

      if (!isset($admin['id'])) {
        return $this->respondWithError('登录帐号不存在！');
      }

      if ($admin['password'] !== md5(SHA1($admin['salt'] . $password))) {
        return $this->respondWithError('帐号密码不正确！');
      }

      if ($admin['status'] == 1) {
        $_SESSION['login_id'] = $admin['id'];
        $_SESSION['role_id'] = $admin['role_id'];
        $_SESSION['groupId'] = $admin['groupId'];
        $_SESSION['user_id'] = $admin['uid'];
        $this->adminRepository->update(['lastLoginTime' => time(), 'lastLoginIp' => $this->getIP()], ['id' => $admin['id']]);
        // 发送公众号通知
        if ($admin['uid'] > 0) {
          $first = '您的账号“' . $admin['account'] . '”刚刚登录了系统；';
          $device = $this->request->getHeaderLine('X-HTTP-Device');
          if ($device) $first .= '登录IP：' . $this->getIP() . '，客户端：' . $device . '。';
          $this->logger->log(0, '通过账号密码登录系统，登录IP：' . $this->getIP() . '，客户端：' . $device . '。');
          $this->logger->info(str_replace('您的账号', '', $first));
          $msgData = [
            'template_id_short' => 'OPENTM411999701',//登录操作通知,所属行业编号21
            'url' => $this->httpHost() . $this->basePath . '/admin/index?tk=' . Crypto::encrypt(session_id(), $this->key),
            'data' => [
              'keyword1' => ['value' => $admin['account'] . '，通过密码登录了' . $this->systemName, 'color' => '#173177'],
              'keyword2' => ['value' => date('Y-m-d') . '，点击详情可修改密码', 'color' => '#173177']
            ]
          ];
          $this->user->sendMessage([$admin['uid']], $msgData);
        }
        $redirect_uri = $this->request->getHeaderLine('Referer');
        if (str_contains($redirect_uri, '/login')) $redirect_uri = $this->httpHost() . $this->basePath . '/#/admin/dashboard';
        return $this->respondWithData(['msg' => '系统登录成功！', 'redirect_uri' => $redirect_uri]);
      } else {
        return $this->respondWithError('帐号已被锁定，无法登录！');
      }
    } else {
      if ($this->getLoginId()) return $this->response->withHeader('Location', $this->httpHost() . $this->basePath . '/#/admin/dashboard')->withStatus(301);
      $code = Crypto::encrypt(session_id(), $this->key);
      $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
      $writer = new Writer($renderer);
      $data['loginQr'] = $writer->writeString($this->httpHost() . $this->basePath . '/qrLogin?tk=' . $code);
      $data['basePath'] = $this->basePath;
      $data['systemName'] = $this->systemName;

      return $this->respondView('admin/login.html', $data);
    }
  }

}
