<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/29
 * Time: 15:05
 */

namespace App\Repositories\Mysql\Weixin;


use Wanphp\Libray\Mysql\Database;
use App\Domain\Weixin\MsgTemplateInterface;
use App\Entities\Weixin\MsgTemplateEntity;
use App\Repositories\Mysql\BaseRepository;

class MsgTemplateRepository extends BaseRepository implements MsgTemplateInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, MsgTemplateEntity::class);
  }

  /**
   * {@inheritDoc}
   */
  public function getTemplateId($id): string
  {
    return $this->get('template_id', ['id' => $id, 'status' => 1]);
  }
}
