<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/8
 * Time: 15:44
 */

namespace App\Repositories\Mysql\Router;


use Wanphp\Libray\Mysql\Database;
use App\Entities\Common\NavigateEntity;
use App\Domain\Common\NavigateInterface;
use App\Repositories\Mysql\BaseRepository;

class NavigateRepository extends BaseRepository implements NavigateInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, NavigateEntity::class);
  }
}
