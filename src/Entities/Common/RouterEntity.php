<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 15:21
 */

namespace App\Entities\Common;

use App\Entities\Traits\EntityTrait;
use JsonSerializable;

/**
 * Class Router
 * @package App\Entities\Common
 * @OA\Schema(
 *   title="系统路由",
 *   description="系统路由数据结构",
 *   required={"name","key","value"}
 * )
 */
class RouterEntity implements JsonSerializable
{
  use EntityTrait;
  /**
   * @DBType({"key": "PRI","type":"smallint(6) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer|null
   */
  private $id;
  /**
   *
   * @DBType({"key": "","type":"smallint(6) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="所在导航菜单")
   * @var integer
   */
  private $navId;
  /**
   * @DBType({"key": "","type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="名称")
   * @var string
   */
  private $name;
  /**
   * @DBType({"key": "UNI","type":"varchar(50) NOT NULL DEFAULT ''"})
   * @OA\Property(description="路由")
   * @var string
   */
  private $route;
  /**
   * @DBType({"key": "","type":"varchar(80) NOT NULL DEFAULT ''"})
   * @OA\Property(description="回调")
   * @var string
   */
  private $callable;
  /**
   * @DBType({"key": "","type":"tinyint(4) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="排序")
   * @var integer
   */
  private $sortOrder;
}
