<?php

namespace App\Entities;

use Wanphp\Libray\Mysql\EntityTrait;

class CacheEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"varchar(100) NOT NULL"})
   * @var string
   * @OA\Property(description="缓存键")
   */
  private string $key;
  /**
   * @DBType({"type":"varchar(500) NOT NULL DEFAULT ''"})
   * @OA\Property(description="配置项名称")
   * @var string
   */
  private string $value;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="过期时间")
   */
  private int $expires_at;
}
