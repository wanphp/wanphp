<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/11/25
 * Time: 20:21
 */

namespace App\Entities\Common;


use Wanphp\Libray\Mysql\EntityTrait;

/**
 * Class FilesEntity
 * @package App\Entities\Common
 * @OA\Schema(title="上传文件",required={"url"})
 */
class FilesEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"int NOT NULL AUTO_INCREMENT"})
   * @var integer|null
   * @OA\Property(format="int64", description="信息ID")
   */
  private ?int $id;
  /**
   * 用户ID
   * @DBType({"key":"MUL","type":"int(11) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(format="int64", description="上传用户ID")
   */
  private int $uid;
  /**
   * @DBType({"type":"varchar(50) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="名称")
   */
  private string $name;
  /**
   * @DBType({"type":"char(32) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="md5值")
   */
  private string $md5;
  /**
   * @DBType({"type":"varchar(120) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="文件地址")
   */
  private string $url;
  /**
   * @DBType({"type":"int(11) NOT NULL DEFAULT '0'"})
   * @var integer
   * @OA\Property(description="文件大小")
   */
  private int $size;
  /**
   * @DBType({"type":"varchar(80) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="文件类型")
   */
  private string $type;
  /**
   * @DBType({"type":"varchar(10) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="文件扩展名")
   */
  private string $extension;
  /**
   * @DBType({"type":"int(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="上传时间")
   */
  private int $uptime;
}
