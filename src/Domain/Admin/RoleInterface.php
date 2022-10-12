<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 17:42
 */

namespace App\Domain\Admin;


use App\Domain\BaseInterface;
use App\Entities\Admin\RoleEntity;
use Exception;

interface RoleInterface extends BaseInterface
{
  const TABLE_NAME = "role";
}
