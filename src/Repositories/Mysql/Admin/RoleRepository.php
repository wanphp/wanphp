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
    parent::__construct($database, self::TABLENAME, RoleEntity::class);
  }

  /**
   * {@inheritDoc}
   */
  public function findRoleOfId($id): RoleEntity
  {
    $role = $this->get('*', ['id' => $id]);
    if (empty($role)) throw new \Exception('找不到角色！');
    return new RoleEntity($role);
  }

  /**
   * {@inheritDoc}
   */
  public function delRole($id): bool
  {
    return $this->delete(['id' => $id]);
  }

}
