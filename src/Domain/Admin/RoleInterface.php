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
  /**
   * @param int $id
   * @return RoleEntity
   * @throws Exception
   */
  public function findRoleOfId(int $id): RoleEntity;

  /**
   * @param int $id
   * @return bool
   * @throws Exception
   */
  public function delRole(int $id): bool;
}
