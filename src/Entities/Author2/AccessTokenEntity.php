<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 15:59
 */

namespace App\Entities\Author2;


use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
  use AccessTokenTrait, TokenEntityTrait, EntityTrait;
}
