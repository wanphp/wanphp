<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 11:50
 */

namespace App\Repositories\Mysql\Common;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\SettingInterface;
use App\Entities\Common\SettingEntity;
use App\Repositories\Mysql\BaseRepository;

class SettingRepository extends BaseRepository implements SettingInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, SettingEntity::class);
  }
}
