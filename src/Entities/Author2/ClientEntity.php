<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 16:17
 */

namespace App\Entities\Author2;


use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
  use EntityTrait, ClientTrait;

  public function __construct(array $data)
  {
    $this->identifier = $data['client_id'];
    $this->name = $data['name'];
    $this->redirectUri = $data['redirect_uri'];
    $this->isConfidential = $data['confidential'];
  }
}
