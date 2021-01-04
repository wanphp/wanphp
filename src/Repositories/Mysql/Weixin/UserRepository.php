<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 17:09
 */

namespace App\Repositories\Mysql\Weixin;


use App\Infrastructure\Database\Database;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Weixin\UserEntity;
use App\Domain\Weixin\UserInterface;
use App\Repositories\Mysql\BaseRepository;

class userRepository extends BaseRepository implements UserInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, UserEntity::class);
  }

  public function findAll(): array
  {
    return $this->select();
  }

  public function findUserOfId(int $id): UserEntity
  {
    $user = $this->get('*', ['id' => $id]);
    if (empty($user)) throw new NotFoundException("找不到用户！");
    return new UserEntity($user);
  }


}
