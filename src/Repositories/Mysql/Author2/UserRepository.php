<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 16:30
 */

namespace App\Repositories\Mysql\Author2;


use App\Domain\Admin\AdminInterface;
use App\Domain\DomainException\MedooException;
use App\Entities\Admin\AdminEntity;
use App\Entities\Author2\UserEntity;
use App\Infrastructure\Database\Database;
use App\Repositories\Mysql\BaseRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, AdminInterface::TABLENAME, AdminEntity::class);
  }

  /**
   * @param string $username
   * @param string $password
   * @param string $grantType
   * @param ClientEntityInterface $clientEntity
   * @return UserEntity|UserEntityInterface|null
   * @throws MedooException
   * @throws OAuthServerException
   */
  public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
  {
    // 验证用户时调用此方法
    // 用于验证用户信息是否符合
    // 可以验证是否为用户可使用的授权类型($grantType)与客户端($clientEntity)
    // 验证成功返回 UserEntityInterface 对象
    $account = trim($username);
    $password = md5(trim($password));

    $admin = $this->get('id,account,password,salt,status', ['account' => $account]);
    if (empty($admin)) {//没有添加过管理员
      throw new OAuthServerException('帐号不存在,请核实！', 3, 'invalid_request', 400);
    }

    if ($admin['password'] !== md5(SHA1($admin['salt'] . $password))) {
      throw new OAuthServerException('帐号密码不正确,请核实！', 3, 'invalid_request', 400);
    }

    if ($admin['status']) {
      $this->update(['lastlogintime' => time(), 'lastloginip' => $_SERVER['REMOTE_ADDR']], ['id' => $admin['id']]);
      $user = new UserEntity();
      $user->setIdentifier('admin_' . $admin['id']);
      return $user;
    } else {
      throw new OAuthServerException('帐号已被锁定,无法认证，请联系管理员！', 3, 'invalid_request', 400);
    }
  }
}
