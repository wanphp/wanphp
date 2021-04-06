<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/17
 * Time: 15:24
 */

namespace App\Repositories\Mysql\Weixin;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Weixin\UserRoleInterface;
use App\Entities\Weixin\UserRoleEntity;
use App\Repositories\Mysql\BaseRepository;

class UserRoleRepository extends BaseRepository implements UserRoleInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, UserRoleEntity::class);
  }
}
