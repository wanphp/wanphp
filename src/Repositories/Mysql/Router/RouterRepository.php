<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 13:52
 */

namespace App\Repositories\Mysql\Router;


use Wanphp\Libray\Mysql\Database;
use App\Entities\Common\RouterEntity;
use App\Domain\Common\RouterInterface;
use App\Repositories\Mysql\BaseRepository;

class RouterRepository extends BaseRepository implements RouterInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, RouterEntity::class);
  }
}
