<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/22
 * Time: 14:38
 */

namespace App\Repositories\Mysql\Common;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\ClientsInterface;
use App\Entities\Common\ClientsEntity;
use App\Repositories\Mysql\BaseRepository;

class ClientsRepository extends BaseRepository implements ClientsInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ClientsEntity::class);
  }
}
