<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/31
 * Time: 16:24
 */

namespace App\Repositories\Mysql\Author2;


use App\Domain\Common\ClientsInterface;
use App\Infrastructure\Database\Database;
use App\Entities\Author2\ClientEntity;
use App\Entities\Common\ClientsEntity;
use App\Repositories\Mysql\BaseRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, ClientsInterface::TABLENAME, ClientsEntity::class);
  }

  /**
   * 获取客户端对象时调用方法，用于验证客户端
   * @param string $clientIdentifier 客户端ID
   * @param string $clientIdentifier
   * @return ClientEntity|ClientEntityInterface|null
   * @throws \App\Domain\DomainException\MedooException
   */
  public function getClientEntity($clientIdentifier)
  {
    $client = $this->get('client_id,name,redirect_uri,confidential', ['client_id' => $clientIdentifier]);
    if ($client) return new ClientEntity($client);
    else return null;
  }

  /**
   * @param string $clientIdentifier 客户端ID
   * @param string|null $clientSecret 客户端密钥
   * @param string|null $grantType 授权类型
   * @return bool
   * @throws \App\Domain\DomainException\MedooException
   */
  public function validateClient($clientIdentifier, $clientSecret, $grantType)
  {
    $client_secret = $this->get('client_secret', ['client_id' => $clientIdentifier]);
    if (in_array($grantType, ['authorization_code', 'client_credentials', 'password', 'refresh_token'])) {
      return $client_secret == $clientSecret;
    } else {
      if ($client_secret) return true;
      else return false;
    }
  }
}
