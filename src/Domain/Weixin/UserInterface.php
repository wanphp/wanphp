<?php
declare(strict_types=1);

namespace App\Domain\Weixin;

use App\Domain\BaseInterface;
use App\Domain\DomainException\MedooException;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Weixin\UserEntity;

interface UserInterface extends BaseInterface
{
  const TABLENAME = "weixin_users";

  /**
   * @return array
   * @throws MedooException
   */
  public function findAll(): array;

  /**
   * @param int $id
   * @return UserEntity
   * @throws NotFoundException
   * @throws MedooException
   */
  public function findUserOfId(int $id): UserEntity;
}
