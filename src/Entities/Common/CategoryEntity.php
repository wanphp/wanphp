<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 11:23
 */

namespace App\Entities\Common;


use App\Entities\Traits\EntityTrait;

class CategoryEntity implements \JsonSerializable
{
  use EntityTrait;
  /**
   * @DBType({"key":"PRI","type":"smallint(6) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer|null
   */
  private $id;
  /**
   * 分组,如：article,link
   * @DBType({"key":"MUL","type":"varchar(20) NOT NULL DEFAULT ''"})
   * @var string
   */
  private $code;
  /**
   * @DBType({"key":"MUL","type":"smallint(6) NOT NULL DEFAULT 0"})
   * @OA\Property(description="父类ID")
   * @var integer
   */
  private $parent_id;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端名称")
   * @var string
   */
  private $name;
  /**
   * @DBType({"type":"varchar(200) NOT NULL DEFAULT ''"})
   * @OA\Property(description="父结点路径")
   * @var string
   */
  private $parent_path;
  /**
   * @DBType({"type":"tinyint(4) NOT NULL DEFAULT ''"})
   * @OA\Property(description="深度")
   * @var string
   */
  private $deep;
  /**
   * @DBType({"type":"smallint(6) NOT NULL DEFAULT 0"})
   * @OA\Property(description="排序")
   * @var integer
   */
  private $sortOrder;
}
