<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 21:27
 */

namespace App\Domain\Common;


use App\Domain\BaseInterface;

interface LogsInterface extends BaseInterface
{
  const TABLE_NAME = "logs";

  public function insertLog(array $data): int;
}
