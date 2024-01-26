<?php

namespace App\Repositories\Mysql\Common;


use App\Domain\Article\BasicLogInterface;
use App\Entities\Common\LogsEntity;
use Wanphp\Libray\Mysql\Database;
use App\Domain\Common\LogsInterface;
use App\Repositories\Mysql\BaseRepository;

class LogsRepository extends BaseRepository implements LogsInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, LogsEntity::class);
  }

  /**
   * @throws \Exception
   */
  public function insertLog(array $data): int
  {
    $log_id = $this->insert($data);
    if (isset($data['basic_id']) && $log_id > 0) {
      $this->db->insert(BasicLogInterface::TABLE_NAME, ['basic_id' => $data['basic_id'], 'log_id' => $log_id]);
    }
    return $log_id;
  }
}
