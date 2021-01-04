<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 11:35
 */

namespace App\Repositories\Mysql\Common;


use App\Domain\Common\CategoryInterface;
use App\Entities\Common\CategoryEntity;
use App\Infrastructure\Database\Database;
use App\Repositories\Mysql\BaseRepository;

class CategoryRepository extends BaseRepository implements CategoryInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, CategoryEntity::class);
  }
}
