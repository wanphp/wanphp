<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/29
 * Time: 15:05
 */

namespace App\Repositories\Mysql\Weixin;


use App\Domain\Weixin\MsgTemplateInterface;
use App\Entities\Weixin\MsgTemplateEntity;
use App\Infrastructure\Database\Database;
use App\Repositories\Mysql\BaseRepository;

class MsgTemplateRepository extends BaseRepository implements MsgTemplateInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, MsgTemplateEntity::class);
  }

  /**
   * @param int $id
   * @return string
   * @throws \App\Domain\DomainException\MedooException
   */
  public function getTemplateId($id): string
  {
    return $this->get('template_id', ['id' => $id, 'status' => 1]);
  }
}
