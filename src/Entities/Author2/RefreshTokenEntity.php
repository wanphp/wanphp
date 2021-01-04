<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 16:18
 */

namespace App\Entities\Author2;


use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
  use RefreshTokenTrait, EntityTrait;
}
