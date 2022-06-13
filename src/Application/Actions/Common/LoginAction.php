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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Weixin\WeChatBase;
use Wanphp\Plugins\Weixin\Domain\PublicInterface;

class LoginAction extends Action
{
  private AdminInterface $adminRepository;
  private WeChatBase $weChatBase;
  private PublicInterface $public;
  private Key $key;

  /**
   * @param LoggerInterface $logger
   * @param ContainerInterface $container
   * @param AdminInterface $adminRepository
   * @throws BadFormatException
   * @throws EnvironmentIsBrokenException
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(
    LoggerInterface    $logger,
    ContainerInterface $container,
    AdminInterface     $adminRepository,
    WeChatBase         $weChatBase,
    PublicInterface    $public
  )
  {
    parent::__construct($logger);
    $this->adminRepository = $adminRepository;
    $this->weChatBase = $weChatBase;
    $this->public = $public;
    $this->key = Key::loadFromAsciiSafeString($container->get('settings')['encryptionKey']);
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
        return $this->respondWithData(['msg' => '系统初始化并登录成功！']);
      } else {
        $admin = $this->adminRepository->get('id,uid,account,salt,password,role_id,status', ['OR' => ['account' => $account, 'tel' => $account]]);
      }

      if (!isset($admin['id'])) {
        return $this->respondWithError('登录帐号不存在！');
      }

      if ($admin['password'] !== md5(SHA1($admin['salt'] . $password))) {
        return $this->respondWithError('帐号密码不正确！');
      }

      $params = $this->request->getServerParams();
      if ($admin['status'] == 1) {
        $_SESSION['login_id'] = $admin['id'];
        $_SESSION['role_id'] = $admin['role_id'];
        $_SESSION['user_id'] = $admin['uid'];
        $this->adminRepository->update(['lastlogintime' => time(), 'lastloginip' => $params['REMOTE_ADDR']], ['id' => $admin['id']]);
        // 发送公众号通知
        if ($admin['uid'] > 0) {
          $first = '您的账号“' . $admin['account'] . '”刚刚登录了系统；';
          $device = $this->request->getHeaderLine('X-HTTP-Device');
          if ($device) $first .= '登录IP：' . $params['REMOTE_ADDR'] . '，客户端：' . $device . '。';
          $this->logger->info(str_replace('您的账号', '', $first));
          $msgdata = [
            'touser' => $this->public->get('openid', ['id' => $admin['uid']]),
            'template_id' => '', //TODO 自行绑定公众号模板消息ID
            'url' => $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/admin/index?tk=' . Crypto::encrypt(session_id(), $this->key),
            'data' => [
              'first' => ['value' => $first, 'color' => '#173177'],
              'keyword1' => ['value' => $admin['account'], 'color' => '#173177'],
              'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#173177'],
              'remark' => ['value' => '如果不是您本人登录，查看详情及时修改登录密码，并联系管理员。', 'color' => '#173177']
            ]
          ];
          $this->weChatBase->sendTemplateMessage($msgdata);
        }
        $redirect_uri = $this->request->getHeaderLine('Referer');
        if (str_contains($redirect_uri, '/login')) $redirect_uri = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/admin/index';
        return $this->respondWithData(['msg' => '系统登录成功！', 'redirect_uri' => $redirect_uri]);
      } else {
        return $this->respondWithError('帐号已被锁定，无法登录！');
      }
    } else {
      $code = Crypto::encrypt(session_id(), $this->key);
      $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
      $writer = new Writer($renderer);
      $data['loginQr'] = $writer->writeString($this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/qrlogin?tk=' . $code);

      return $this->respondView('admin/login.html', $data);
    }
  }

}
