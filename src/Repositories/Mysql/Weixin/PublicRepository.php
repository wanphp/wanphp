<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 10:46
 */

namespace App\Repositories\Mysql\Weixin;


use App\Domain\Weixin\PublicInterface;
use App\Entities\Weixin\PublicEntity;
use App\Infrastructure\Database\Database;
use App\Repositories\Mysql\BaseRepository;

class PublicRepository extends BaseRepository implements PublicInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, PublicEntity::class);
  }
}
