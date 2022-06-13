<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 15:52
 */

namespace App\Domain\Common;


use App\Domain\BaseInterface;
use App\Entities\Common\RouterEntity;
use Exception;

interface RouterInterface extends BaseInterface
{
  const TABLE_NAME = "routers";

  /**
   * @param int $id
   * @return RouterEntity
   * @throws Exception
   */
  public function findActionOfId(int $id): RouterEntity;

}
