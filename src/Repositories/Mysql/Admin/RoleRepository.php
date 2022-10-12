<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/10
 * Time: 16:12
 */

namespace App\Repositories\Mysql\Admin;


use Wanphp\Libray\Mysql\Database;
use App\Entities\Admin\RoleEntity;
use App\Domain\Admin\RoleInterface;
use App\Repositories\Mysql\BaseRepository;

class RoleRepository extends BaseRepository implements RoleInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, RoleEntity::class);
  }
}
