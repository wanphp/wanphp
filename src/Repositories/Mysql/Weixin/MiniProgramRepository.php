<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 10:54
 */

namespace App\Repositories\Mysql\Weixin;


use App\Domain\Weixin\MiniProgramInterface;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Weixin\MiniProgram;
use App\Repositories\Mysql\BaseRepository;

class MiniProgramRepository extends BaseRepository implements MiniProgramInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLENAME, MiniProgram::class);
  }
}
