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

class LoginAction extends Action
{
  private AdminInterface $adminRepository;
  private Key $key;

  /**
   * @param LoggerInterface $logger
   * @param ContainerInterface $container
   * @param AdminInterface $adminRepository
   * @throws BadFormatException
   * @throws ContainerExceptionInterface
   * @throws EnvironmentIsBrokenException
   * @throws NotFoundExceptionInterface
   */
  public function __construct(
    LoggerInterface    $logger,
    ContainerInterface $container,
    AdminInterface     $adminRepository
  )
  {
    parent::__construct($logger);
    $this->adminRepository = $adminRepository;
    $this->key = Key::loadFromAsciiSafeString($container->get('oauth2Config')['encryptionKey']);
  }

  protected function action(): Response
  {
    //print_r($this);exit();
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
          'role_id' => [-1],
          'status' => 1,
          'createtime' => time()
        ]);
        $_SESSION['login_id'] = $id;
        $_SESSION['role_id'] = [-1];
        return $this->respondWithData(['msg' => '系统初始化并登录成功！']);
      } else {
        $admin = $this->adminRepository->get('id,uid,account,salt,password,role_id[JSON],status', ['OR' => ['account' => $account, 'tel' => $account]]);
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
