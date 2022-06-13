<?php

namespace App\Repositories\Mysql\Weixin;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\Weixin\Domain\PublicInterface;
use Wanphp\Plugins\Weixin\Domain\UserInterface;

class UserRepository extends \Wanphp\Plugins\Weixin\Repositories\UserRepository implements \App\Domain\Weixin\UserInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database);
  }

  public function user($id): bool|array
  {
    return $this->db->select(UserInterface::TABLE_NAME . '(u)', [
      '[>]' . PublicInterface::TABLE_NAME . '(p)' => ["u.id" => "id"]
    ],
      ['u.nickname', 'u.headimgurl', 'u.name', 'u.tel', 'u.role_id','p.tagid_list[JSON]', 'p.parent_id'],
      ['u.id' => $id]
    ) ?: [];
  }

  public function getUsers($where): array
  {
    return $this->db->select(UserInterface::TABL_ENAME . '(u)', [
      '[>]' . PublicInterface::TABLE_NAME . '(p)' => ["u.id" => "id"]
    ],
      ['u.id', 'u.nickname', 'u.headimgurl', 'u.name', 'u.tel', 'u.role_id','p.openid','p.tagid_list[JSON]', 'p.subscribe', 'p.parent_id'],
      $where
    ) ?: [];
  }
}