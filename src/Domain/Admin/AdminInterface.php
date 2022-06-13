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

  /**
   * @param int $id
   * @return AdminEntity
   * @throws Exception
   */
  public function findAdminOfId(int $id): AdminEntity;

  /**
   * @param int $id
   * @return int
   * @throws Exception
   */
  public function delAdmin(int $id): int;

  public function getAdminList(array $columns, $where): array;

  public function adminCount(string $columns, $where): ?int;
}
