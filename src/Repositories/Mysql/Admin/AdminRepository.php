<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/4
 * Time: 15:42
 */

namespace App\Repositories\Mysql\Admin;


use App\Infrastructure\Database\Database;
use App\Domain\Admin\AdminInterface;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Admin\AdminEntity;
use App\Repositories\Mysql\BaseRepository;

class AdminRepository extends BaseRepository implements AdminInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, AdminEntity::class);
  }

  public function findAdminOfId(int $id): AdminEntity
  {
    $admin = $this->get('*', ['id' => $id]);
    if (empty($admin)) throw new NotFoundException('找不到管理员！');
    return new AdminEntity($admin);
  }

  public function delAdmin(int $id): int
  {
    return $this->delete(['id' => $id]);
  }

}
