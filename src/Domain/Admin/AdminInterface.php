<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 16:41
 */

namespace App\Domain\Admin;


use App\Domain\BaseInterface;
use App\Entities\Admin\AdminEntity;
use Exception;

interface AdminInterface extends BaseInterface
{
  const TABLE_NAME = "admini";
}
