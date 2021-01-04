<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/8
 * Time: 15:44
 */

namespace App\Repositories\Mysql\Router;


use App\Infrastructure\Database\Database;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Common\NavigateEntity;
use App\Domain\Common\NavigateInterface;
use App\Repositories\Mysql\BaseRepository;

class NavigateRepository extends BaseRepository implements NavigateInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, NavigateEntity::class);
  }

  public function findNavigateOfId(int $id): NavigateEntity
  {
    $navigate = $this->get('*', ['id' => $id]);
    if (empty($navigate)) throw new NotFoundException('找不到导航菜单！');
    return new NavigateEntity($navigate);
  }

  public function delNavigate(int $id): int
  {
    return $this->delete(['id' => $id]);
  }

}
