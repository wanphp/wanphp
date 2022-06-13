<?php

namespace App\Domain\Weixin;

interface UserInterface extends \Wanphp\Plugins\Weixin\Domain\UserInterface
{
  public function user($id);
  public function getUsers($where);
}