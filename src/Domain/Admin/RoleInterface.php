<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 17:42
 */

namespace App\Domain\Admin;


use App\Domain\BaseInterface;
use App\Domain\DomainException\MedooException;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Admin\RoleEntity;

interface RoleInterface extends BaseInterface
{
  const TABLENAME = "role";
  /**
   * @param int $id
   * @return RoleEntity
   * @throws NotFoundException
   * @throws MedooException
   */
  public function findRoleOfId(int $id): RoleEntity;

  /**
   * @param int $id
   * @return bool
   * @throws MedooException
   */
  public function delRole(int $id): bool;
}
