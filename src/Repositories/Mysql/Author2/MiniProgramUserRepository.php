<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/21
 * Time: 11:41
 */

namespace App\Repositories\Mysql\Author2;


use App\Domain\Weixin\MiniProgramInterface;
use App\Domain\Weixin\UserInterface;
use App\Entities\Author2\UserEntity;
use App\Entities\Weixin\UserEntity as WeUserEntity;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Weixin\MiniProgram;
use App\Repositories\Mysql\BaseRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class MiniProgramUserRepository extends BaseRepository implements UserRepositoryInterface
{
  private $miniProgram;
  private $miniProgramUser;

  public function __construct(MiniProgram $miniProgram, MiniProgramInterface $miniProgramUser, Database $database)
  {
    parent::__construct($database, UserInterface::TABLENAME, WeUserEntity::class);
    $this->miniProgram = $miniProgram;
    $this->miniProgramUser = $miniProgramUser;
  }

  /**
   * @return UserEntityInterface
   */
  /**
   * @param string $username
   * @param string $password
   * @param string $grantType
   * @param ClientEntityInterface $clientEntity
   * @return UserEntity|array|UserEntityInterface|null
   * @throws \Exception
   */
  public function getUserEntityByUserCredentials(
    $username,
    $password,
    $grantType,
    ClientEntityInterface $clientEntity
  )
  {
    // 验证用户时调用此方法
    // 用于验证用户信息是否符合
    // 可以验证是否为用户可使用的授权类型($grantType)与客户端($clientEntity)
    // 验证成功返回 UserEntityInterface 对象
    $res = $this->miniProgram->code2Session($password);
    if (isset($res['openid'])) {
      //用户数据
      if (is_string($username)) {
        $data = json_decode($username, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          $data['unionid'] = $res['unionid'] ?? '';
        } else {
          $data = ['unionid' => $res['unionid']] ?? '';
        }
      }
      if (is_array($username)) {
        $data = $username;
        $data['unionid'] = $res['unionid'] ?? '';
      }
      if (isset($res['unionid'])) $user = $this->get('id', ['unionid' => $res['unionid']]);
      if (!isset($user['id'])) $user = $this->miniProgramUser->get('id', ['openid' => $res['openid']]);
      if (!isset($user['id'])) {//添加用户
        if (is_array($data)) $user['id'] = $this->insert($data);
        //关联小程序数据
        $xcxdata = ['id' => $user['id'], 'openid' => $res['openid']];
        if (isset($data['parent_id'])) $xcxdata['parent_id'] = $data['parent_id'];
        $this->miniProgramUser->insert($xcxdata);
      } else {//更新用户
        if (is_array($data)) $this->update($data, ['id' => $user['id']]);
      }
      if ($user['id'] > 0) {
        $userEntity = new UserEntity();
        $userEntity->setIdentifier($user['id']);
        return $userEntity;
      } else {
        return null;
      }
    }
    return null;
  }
}
