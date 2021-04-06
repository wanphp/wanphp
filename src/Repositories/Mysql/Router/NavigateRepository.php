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
    parent::__construct($database, self::TABLENAME, NavigateEntity::class);
  }
  /**
   * {@inheritDoc}
   */
  public function findNavigateOfId($id): NavigateEntity
  {
    $navigate = $this->get('*', ['id' => $id]);
    if (empty($navigate)) throw new \Exception('找不到导航菜单！');
    return new NavigateEntity($navigate);
  }

  /**
   * {@inheritDoc}
   */
  public function delNavigate($id): int
  {
    return $this->delete(['id' => $id]);
  }

}
