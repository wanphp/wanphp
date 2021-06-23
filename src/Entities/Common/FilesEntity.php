<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/11/25
 * Time: 20:21
 */

namespace App\Entities\Common;


use App\Entities\Traits\EntityTrait;

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
  private $id;
  /**
   * 用户ID
   * @DBType({"key":"MUL","type":"int(11) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(format="int64", description="上传用户ID")
   */
  private $uid;
  /**
   * @DBType({"type":"varchar(50) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="标题")
   */
  private $name;
  /**
   * 图片md5，上传图片时验证,redis存key=>value,img_md5=>img_url
   * @DBType({"type":"char(32) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="图片md5值")
   */
  private $md5;
  /**
   * url 组成 /YM(一年12个目录)/文件最多65535个，（一个月最多可上传65535个文件）
   * @DBType({"type":"varchar(120) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="文件地址")
   */
  private $url;
  /**
   * @DBType({"type":"int(11) NOT NULL DEFAULT '0'"})
   * @var integer
   * @OA\Property(description="文件大小")
   */
  private $size;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="文件类型")
   */
  private $type;
  /**
   * @DBType({"type":"varchar(10) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="图片扩展名")
   */
  private $extension;
  /**
   * @DBType({"type":"int(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="上传时间")
   */
  private $uptime;
}
