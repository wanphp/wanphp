<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 17:49
 */

namespace App\Entities\Common;


use Wanphp\Libray\Mysql\EntityTrait;

/**
 * Class Navigate
 * @package App\Entities\Common
 * @OA\Schema(
 *   title="系统导航",
 *   description="系统导航数据结构",
 *   required={"icon","name"}
 * )
 */
class NavigateEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * 主键
   * @DBType({"key": "PRI","type":"tinyint(4) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"key": "","type":"varchar(30) NOT NULL DEFAULT ''"})
   * @OA\Property(description="图标样式")
   * @var string
   */
  private string $icon;
  /**
   * @DBType({"key": "","type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="导航名称")
   * @var string
   */
  private string $name;
  /**
   * @DBType({"key": "","type":"tinyint(4) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="排序")
   * @var integer
   */
  private int $sortOrder;
}
