<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/4
 * Time: 15:42
 */

namespace App\Repositories\Mysql\Admin;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Admin\AdminInterface;
use App\Entities\Admin\AdminEntity;
use App\Repositories\Mysql\BaseRepository;

class AdminRepository extends BaseRepository implements AdminInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, AdminEntity::class);
  }

  /**
   * {@inheritDoc}
   */
  public function findAdminOfId($id): AdminEntity
  {
    $admin = $this->get('*', ['id' => $id]);
    if (empty($admin)) throw new \Exception('找不到管理员！');
    return new AdminEntity($admin);
  }

  /**
   * {@inheritDoc}
   */
  public function delAdmin($id): int
  {
    return $this->delete(['id' => $id]);
  }

}
