<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 13:52
 */

namespace App\Repositories\Mysql\Router;


use App\Infrastructure\Database\Database;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Common\RouterEntity;
use App\Domain\Common\RouterInterface;
use App\Repositories\Mysql\BaseRepository;

class RouterRepository extends BaseRepository implements RouterInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, RouterEntity::class);
  }

  public function findActionOfId(int $id): RouterEntity
  {
    $action = $this->get('*', ['id' => $id]);
    if (empty($role)) throw new NotFoundException('找不到路由！');
    return new RouterEntity($action);
  }

}
