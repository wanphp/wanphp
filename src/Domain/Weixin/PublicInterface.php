<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/16
 * Time: 10:47
 */

namespace App\Domain\Weixin;


use App\Domain\BaseInterface;

interface  PublicInterface extends BaseInterface
{
  const TABLENAME = "weixin_users_public";
}
