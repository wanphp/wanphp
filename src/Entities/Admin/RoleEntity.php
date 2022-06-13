<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 17:11
 */

namespace App\Entities\Admin;


use Wanphp\Libray\Mysql\EntityTrait;

/**
 * Class RoleEntity
 * @package App\Entities\Admin
 * @OA\Schema(
 *   title="系统管理角色",
 *   description="系统管理角色数据结构",
 *   required={"name"}
 * )
 */
class RoleEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"tinyint(4) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="角色名称")
   * @var string
   */
  private string $name;
  /**
   * @DBType({"type":"varchar(100) NOT NULL DEFAULT ''"})
   * @OA\Property(description="限制权限")
   * @var string
   */
  private string $restricted;
}
