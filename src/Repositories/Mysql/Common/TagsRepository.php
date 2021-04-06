<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 11:37
 */

namespace App\Repositories\Mysql\Common;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\TagsInterface;
use App\Entities\Common\TagsEntity;
use App\Repositories\Mysql\BaseRepository;

class TagsRepository extends BaseRepository implements TagsInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, TagsEntity::class);
  }
}
