<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/26
 * Time: 18:00
 */

namespace App\Domain\Common;


use App\Domain\BaseInterface;
use App\Domain\DomainException\MedooException;
use App\Domain\DomainException\NotFoundException;
use App\Entities\Common\NavigateEntity;

interface NavigateInterface extends BaseInterface
{
  const TABLENAME = "navigate";

  /**
   * @param int $id
   * @return NavigateEntity
   * @throws NotFoundException
   * @throws MedooException
   */
  public function findNavigateOfId(int $id): NavigateEntity;

  /**
   * @param int $id
   * @return int
   * @throws MedooException
   */
  public function delNavigate(int $id): int;
}
