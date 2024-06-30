<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/3
 * Time: 14:45
 */

namespace App\Application\Actions\Home;


use App\Application\Actions\Action;
use App\Domain\Admin\AdminInterface;
use App\Repositories\Mysql\Router\PersistenceRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\WpUserInterface;

class HomeAction extends Action
{
  private AdminInterface $admin;
  private WpUserInterface $user;
  private PersistenceRepository $persistenceRepository;

  public function __construct(AdminInterface $admin, WpUserInterface $user, PersistenceRepository $persistenceRepository, LoggerInterface $logger)
  {
    $this->admin = $admin;
    $this->user = $user;
    $this->persistenceRepository = $persistenceRepository;
    parent::__construct($logger);
  }

  protected function action(): Response
  {
    // 绑定用户
    $admin = $this->admin->get('id,uid,account,name,tel', ['id' => $_SESSION['login_id']]);
    if (isset($admin['uid']) && $admin['uid'] > 0) {
      $user = $this->user->getUser($admin['uid']);
      if ($user) $admin = array_merge($user, $admin);
    }
    $data = [
      'title' => '管理首页',
      'sidebar' => $this->persistenceRepository->getSidebar(),
      'loginUser' => $admin
    ];

    return $this->respondView('admin/index.html', $data);
  }

}
