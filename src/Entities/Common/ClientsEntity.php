<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/1
 * Time: 10:18
 */

namespace App\Entities\Common;


use App\Entities\Traits\EntityTrait;

/**
 * Class ClientsEntity
 * @package App\Entities\Common
 * @OA\Schema(
 *   title="客户端",
 *   description="客户端数据结构",
 *   required={"name","client_id","client_secret"}
 * )
 */
class ClientsEntity implements \JsonSerializable
{
  use EntityTrait;
  /**
   * @DBType({"key":"PRI","type":"smallint(6) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int32", description="ID")
   * @var integer|null
   */
  private $id;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端名称")
   * @var string
   */
  private $name;
  /**
   * @DBType({"key":"UNI","type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端ID")
   * @var string
   */
  private $client_id;
  /**
   * @DBType({"type":"char(32) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端密钥")
   * @var string
   */
  private $client_secret;
  /**
   * @DBType({"type":"varchar(100) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端回调URL")
   * @var string
   */
  private $redirect_uri;
  /**
   * @DBType({"type":"varchar(50) NOT NULL DEFAULT ''"})
   * @OA\Property(description="客户端IP")
   * @var string
   */
  private $client_ip;
  /**
   * @DBType({"type":"tinyint(1) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="是否机密")
   * @var integer
   */
  private $confidential;
}
