<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/11/25
 * Time: 20:21
 */

namespace App\Entities\Common;


use Wanphp\Libray\Mysql\EntityTrait;

class LogsEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"int NOT NULL AUTO_INCREMENT"})
   * @var integer|null
   * @OA\Property(format="int64", description="日志ID")
   */
  private ?int $log_id;
  /**
   * 用户ID
   * @DBType({"key":"MUL","type":"int(11) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(format="int64", description="用户ID")
   */
  private int $admin_id;
  /**
   * @DBType({"type":"varchar(200) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="日志内容")
   */
  private string $log_content;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="记录时间")
   */
  private int $ctime;
}
