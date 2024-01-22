<?php

namespace App\Repositories\Mysql\Admin;


use App\Domain\Admin\AdminGroupInterface;
use App\Entities\Admin\AdminGroupEntity;
use Wanphp\Libray\Mysql\Database;
use App\Repositories\Mysql\BaseRepository;

class AdminGroupRepository extends BaseRepository implements AdminGroupInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, AdminGroupEntity::class);
  }
}
