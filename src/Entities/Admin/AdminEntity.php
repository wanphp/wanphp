<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 16:17
 */

namespace App\Entities\Admin;


use Wanphp\Libray\Mysql\EntityTrait;

/**
 * Class Admin
 * @package App\Entities\Admin
 * @OA\Schema(
 *   title="系统管理员",
 *   description="系统管理员数据结构",
 *   required={"account","password"}
 * )
 */
class AdminEntity implements \JsonSerializable
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
   * @OA\Property(description="帐号")
   * @var string
   */
  private string $account;
  /**
   * @DBType({"type":"char(32) NOT NULL DEFAULT ''"})
   * @OA\Property(description="密码")
   * @var string
   */
  private string $password;
  /**
   * @DBType({"type":"smallint(6) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="添加管理员的管理员id")
   */
  private int $parentId;
  /**
   * @DBType({"key": "UNI","type":"int(11) NULL DEFAULT NULL"})
   * @OA\Property(description="绑定用户")
   * @var integer
   */
  private int $uid;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="姓名")
   * @var string
   */
  private string $name;
  /**
   * @DBType({"key": "UNI","type":"varchar(20) NULL DEFAULT NULL"})
   * @OA\Property(description="电话")
   * @var string
   */
  private string $tel;
  /**
   * @DBType({"type":"char(11) NOT NULL DEFAULT ''"})
   * @OA\Property(description="加密密钥")
   * @var string
   */
  private string $salt;
  /**
   *
   * @DBType({"type":"smallint(6) NOT NULL DEFAULT 0"})
   * @OA\Property(description="角色")
   * @var int
   */
  private int $role_id;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT ''"})
   * @OA\Property(description="最后登录时间")
   * @var int
   */
  private int $lastLoginTime;
  /**
   * @DBType({"type":"char(15) NOT NULL DEFAULT ''"})
   * @OA\Property(description="最后登录IP")
   * @var string
   */
  private string $lastLoginIp;
  /**
   * @DBType({"type":"char(1) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="帐号状态")
   * @var integer
   */
  private int $status;
  /**
   *
   * @DBType({"type":"char(10) NOT NULL DEFAULT ''"})
   * @OA\Property(description="创建时间")
   * @var integer
   */
  private int $ctime;
}
