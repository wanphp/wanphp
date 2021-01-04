<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 16:41
 */

namespace App\Domain\Admin;


use App\Domain\BaseInterface;
use App\Domain\DomainException\MedooException;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Admin\AdminEntity;

interface AdminInterface extends BaseInterface
{
  const TABLENAME = "admini";
  /**
   * @param int $id
   * @return AdminEntity
   * @throws NotFoundException
   * @throws MedooException
   */
  public function findAdminOfId(int $id): AdminEntity;

  /**
   * @param int $id
   * @return int
   * @throws MedooException
   */
  public function delAdmin(int $id): int;

}
