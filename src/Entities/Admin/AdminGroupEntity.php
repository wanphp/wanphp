<?php

namespace App\Entities\Admin;


use Wanphp\Libray\Mysql\EntityTrait;

/**
 * Class Admin
 * @package App\Entities\Admin
 * @OA\Schema(
 *   title="管理员分组",
 *   description="系统管理员分组数据结构",
 *   required={"account","password"}
 * )
 */
class AdminGroupEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key": "PRI","type":"smallint(4) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="管理员ID")
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"key": "UNI","type":"varchar(50) NOT NULL DEFAULT ''"})
   * @OA\Property(description="分组名称")
   * @var string
   */
  private string $name;
  /**
   * @DBType({"type":"varchar(300) NOT NULL DEFAULT ''"})
   * @OA\Property(description="分组说明")
   * @var string
   */
  private string $description;
  /**
   *
   * @DBType({"type":"char(10) NOT NULL DEFAULT ''"})
   * @OA\Property(description="显示排序")
   * @var integer
   */
  private int $displayOrder;
}
