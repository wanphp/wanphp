<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/29
 * Time: 15:03
 */

namespace App\Domain\Weixin;


use App\Domain\BaseInterface;

interface MsgTemplateInterface extends BaseInterface
{
  const TABLENAME = "msg_template";

  /**
   * @param int $id
   * @return string
   * @throws \Exception
   */
  public function getTemplateId(int $id): string;
}
