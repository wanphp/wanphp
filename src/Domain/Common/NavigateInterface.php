<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 18:00
 */

namespace App\Domain\Common;


use App\Domain\BaseInterface;
use App\Entities\Common\NavigateEntity;
use Exception;

interface NavigateInterface extends BaseInterface
{
  const TABLE_NAME = "navigate";
}
