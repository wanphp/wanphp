<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/10
 * Time: 16:12
 */

namespace App\Repositories\Mysql\Admin;


use App\Infrastructure\Database\Database;
use App\Entities\Admin\RoleEntity;
use App\Domain\Admin\RoleInterface;
use App\Domain\DomainException\NotFoundException;
use App\Repositories\Mysql\BaseRepository;

class RoleRepository extends BaseRepository implements RoleInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, RoleEntity::class);
  }

  public function findRoleOfId(int $id): RoleEntity
  {
    $role = $this->get('*', ['id' => $id]);
    if (empty($role)) throw new NotFoundException('找不到角色！');
    return new RoleEntity($role);
  }

  public function delRole(int $id): bool
  {
    return $this->delete(['id' => $id]);
  }

}
