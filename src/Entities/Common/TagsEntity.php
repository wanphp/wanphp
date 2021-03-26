<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/7
 * Time: 13:53
 */

namespace App\Entities\Common;


use App\Entities\Traits\EntityTrait;

/**
 * Class Tags
 * @package App\Entities\Common
 *  @OA\Schema(
 *   title="标签",
 *   description="分类标签",
 *   required={"name"}
 * )
 */
class TagsEntity implements \JsonSerializable
{
  use EntityTrait;
  /**
   * @DBType({"key":"PRI","type":"smallint NOT NULL AUTO_INCREMENT"})
   * @OA\Property(description="标签ID")
   * @var integer
   */
  private $id;
  /**
   * 分组,如：article,link
   * @DBType({"key":"MUL","type":"varchar(20) NOT NULL DEFAULT ''"})
   * @var string
   */
  private $code;
  /**
   * @DBType({"type":"varchar(30) NOT NULL DEFAULT ''"})
   * @OA\Property(description="标签名称")
   * @var string
   */
  private $name;
  /**
   * @DBType({"type":"smallint(6) NOT NULL DEFAULT 0"})
   *  @OA\Property(description="排序")
   * @var integer
   */
  private $sortOrder;
}
