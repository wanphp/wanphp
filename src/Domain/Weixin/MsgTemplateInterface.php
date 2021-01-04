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

  public function getTemplateId(int $id): string;
}
