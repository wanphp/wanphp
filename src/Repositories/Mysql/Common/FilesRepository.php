<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 11:38
 */

namespace App\Repositories\Mysql\Common;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\FilesInterface;
use App\Entities\Common\FilesEntity;
use App\Repositories\Mysql\BaseRepository;

class FilesRepository extends BaseRepository implements FilesInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, FilesEntity::class);
  }
}
