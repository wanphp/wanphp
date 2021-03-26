<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2021/3/9
 * Time: 10:50
 */

namespace App\Repositories\Mysql\Weixin;


use App\Domain\Weixin\CustomMenuInterface;
use App\Entities\Weixin\CustomMenuEntity;
use App\Infrastructure\Database\Database;
use App\Repositories\Mysql\BaseRepository;

class CustomMenuRepository extends BaseRepository implements CustomMenuInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, CustomMenuEntity::class);
  }
}
