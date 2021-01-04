<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 16:19
 */

namespace App\Entities\Author2;


use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ScopeEntity implements ScopeEntityInterface
{
  use EntityTrait;

  // 没有 Trait 实现这个方法，需要自行实现
  // oauth2-server 项目的测试代码的实现例子
  public function jsonSerialize()
  {
    return $this->getIdentifier();
  }
}
