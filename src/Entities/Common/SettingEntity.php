<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 21:12
 */

namespace App\Entities\Common;


use App\Entities\Traits\EntityTrait;

/**
 * Class Setting
 * @package App\Entities\Common
 * @OA\Schema(
 *   title="系统自定义配置",
 *   description="系统配置数据结构",
 *   required={"name","key","value"}
 * )
 */
class SettingEntity implements \JsonSerializable
{
  use EntityTrait;
  /**
   * @DBType({"key":"PRI","type":"smallint NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer
   */
  private $id;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="配置项名称")
   * @var string
   */
  private $name;
  /**
   * @DBType({"key":"PRI","type":"varchar(30) not null DEFAULT ''"})
   * @OA\Property(description="配置项键")
   * @var string
   */
  private $key;
  /**
   * @DBType({"type":"varchar(300) not null DEFAULT ''"})
   * @OA\Property(description="配置项值")
   * @var string
   */
  private $value;
  /**
   * @DBType({"type":" tinyint(4) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="显示排序")
   * @var integer
   */
  private $display_order;
}
