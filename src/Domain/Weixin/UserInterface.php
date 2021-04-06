<?php
declare(strict_types=1);

namespace App\Domain\Weixin;

use App\Domain\BaseInterface;
use App\Entities\Weixin\UserEntity;

interface UserInterface extends BaseInterface
{
  const TABLENAME = "weixin_users";

  /**
   * @return array
   * @throws \Exception
   */
  public function findAll(): array;

  /**
   * @param int $id
   * @return UserEntity
   * @throws \Exception
   */
  public function findUserOfId(int $id): UserEntity;
}
