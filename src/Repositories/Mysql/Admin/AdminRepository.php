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
    parent::__construct($database, self::TABLE_NAME, AdminEntity::class);
  }
}
